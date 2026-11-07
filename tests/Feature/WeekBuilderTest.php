<?php

declare(strict_types=1);

use App\Models\Dish;
use App\Models\DishNutrition;
use App\Models\Ingredient;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\WeekPlan;
use App\Models\WeekPlanDay;
use App\Models\WeekPlanMeal;
use App\Week\WeekBuilder;
use App\Week\WeekValidator;

// WEEK-001, Phần 2.3: builder + validator. Dữ kiện test tự tạo, không dùng
// số dinh dưỡng thật của kho — builder chỉ cộng số đã có trong DishNutrition.

function week23Dish(string $slug, string $slot, float $protein, int $kcalMin, int $kcalMax, array $opts = []): Dish
{
    $dish = Dish::create(['slug' => $slug, 'country_code' => 'VN', 'source' => 'curated', 'confidence' => 'low', 'completeness_level' => 1]);
    $dish->names()->create(['locale' => 'vi', 'name' => $slug, 'is_primary' => true, 'is_alias' => false]);
    $dish->mealSlots()->create(['slot' => $slot, 'weight' => 8]);
    foreach ($opts['ingredients'] ?? [] as $ingSlug) {
        $ing = Ingredient::where('slug', $ingSlug)->first()
            ?? Ingredient::create(['name_vi' => $ingSlug, 'slug' => $ingSlug, 'category' => $opts['category'] ?? 'khac', 'mapping_confidence' => 'high', 'source' => 'curated', 'confidence' => 'high']);
        $dish->dishIngredients()->create(['ingredient_id' => $ing->id, 'quantity' => 100, 'unit_name' => 'g', 'source' => 'curated']);
    }
    DishNutrition::create([
        'dish_id' => $dish->id, 'basis' => 'per_serving', 'confidence' => 'medium',
        'fct_source' => 'usda_sr_legacy', 'computed_at' => now(), 'engine_version' => '1.1.0', 'source' => 'computed',
        'kcal_min' => $kcalMin, 'kcal_max' => $kcalMax, 'protein_g' => $protein,
        'missing_ingredients' => $opts['missing'] ?? [], 'unverified_ingredients' => $opts['unverified'] ?? [],
    ]);

    return $dish;
}

function week23User(array $profile = []): User
{
    $user = User::factory()->create(['role' => 'user']);
    UserProfile::create(array_merge([
        'user_id' => $user->id, 'goal' => 'duy-tri',
        'target_protein_g_per_day' => 100, 'target_kcal_per_day' => 2000,
    ], $profile));

    return $user;
}

function week23StandardDishes(): void
{
    week23Dish('sang-30', 'sang', 30, 450, 500);
    week23Dish('trua-30', 'trua', 30, 450, 500);
    week23Dish('toi-30', 'toi', 30, 450, 500);
    week23Dish('an-vat-20', 'an-vat', 20, 450, 500);
}

it('builder tạo 7 ngày × 4 bữa, snapshot mục tiêu, validator ghi đạt khi đủ protein và kcal trong khoảng', function () {
    $user = week23User();
    week23StandardDishes();
    $plan = app(WeekBuilder::class)->build($user, '2026-11-09');

    expect($plan->start_date->toDateString())->toBe('2026-11-09')
        ->and($plan->end_date->toDateString())->toBe('2026-11-15')
        ->and($plan->target_protein_g_per_day)->toBe(100)
        ->and($plan->days)->toHaveCount(7)
        ->and($plan->meals)->toHaveCount(28)
        ->and($plan->days->first()->status)->toBe('dat')
        ->and($plan->days->first()->protein_g)->toBe(110.0);
});

it('validator ghi thiếu kèm số thiếu khi đủ 4 bữa nhưng protein chưa tới sàn', function () {
    $user = week23User(['target_protein_g_per_day' => 100]);
    $plan = WeekPlan::create(['user_id' => $user->id, 'start_date' => '2026-11-09', 'end_date' => '2026-11-15', 'target_protein_g_per_day' => 100, 'target_kcal_per_day' => 2000]);
    $day = WeekPlanDay::create(['week_plan_id' => $plan->id, 'date' => '2026-11-09']);
    foreach (['sang', 'trua', 'toi', 'an-vat'] as $slot) {
        $dish = week23Dish($slot.'-20', $slot, 20, 450, 500);
        WeekPlanMeal::create(['week_plan_day_id' => $day->id, 'dish_id' => $dish->id, 'slot' => $slot]);
    }

    $result = app(WeekValidator::class)->validateDay($day, $plan);
    expect($result['status'])->toBe('thieu')
        ->and($result['protein_g'])->toBe(80.0)
        ->and($result['protein_shortfall_g'])->toBe(20);
});

it('validator ghi chưa đủ dữ kiện khi món có nguyên liệu missing, không ghi thiếu hay đạt', function () {
    $user = week23User();
    $dish = week23Dish('trua-thieu', 'trua', 50, 400, 450, ['missing' => ['nghe-tuoi']]);
    $plan = WeekPlan::create(['user_id' => $user->id, 'start_date' => '2026-11-09', 'end_date' => '2026-11-15', 'target_protein_g_per_day' => 100, 'target_kcal_per_day' => 2000]);
    $day = WeekPlanDay::create(['week_plan_id' => $plan->id, 'date' => '2026-11-09']);
    WeekPlanMeal::create(['week_plan_day_id' => $day->id, 'dish_id' => $dish->id, 'slot' => 'trua']);

    $result = app(WeekValidator::class)->validateDay($day, $plan);
    expect($result['status'])->toBe('chua-du-du-lieu')->and($result['unknown_meals'])->toBe(1);
});

it('builder không chọn món chứa nguyên liệu người dùng loại trừ', function () {
    $user = week23User(['excluded_ingredients' => ['lac-rang']]);
    week23Dish('trua-lac', 'trua', 50, 450, 500, ['ingredients' => ['lac-rang']]);
    week23Dish('trua-sach', 'trua', 30, 450, 500);
    week23Dish('sang-30', 'sang', 30, 450, 500);
    week23Dish('toi-30', 'toi', 30, 450, 500);
    week23Dish('an-vat-20', 'an-vat', 20, 450, 500);

    $plan = app(WeekBuilder::class)->build($user, '2026-11-09');
    $trua = $plan->days->first()->meals->firstWhere('slot', 'trua');

    expect($trua->dish->slug)->toBe('trua-sach');
});

it('API tuần ăn chỉ trả tuần của chính người dùng', function () {
    $user = week23User();
    week23StandardDishes();
    $this->actingAs($user);

    $this->postJson('/api/week-plans', ['start_date' => '2026-11-09'])->assertCreated()
        ->assertJsonPath('days_count', 7)->assertJsonPath('meals_count', 28);
    $planId = WeekPlan::where('user_id', $user->id)->first()->id;
    $this->getJson('/api/week-plans/'.$planId)->assertOk()
        ->assertJsonPath('plan.summary.dat', 7);

    $other = User::factory()->create(['role' => 'user']);
    $this->actingAs($other);
    $this->getJson('/api/week-plans/'.$planId)->assertNotFound();
});
