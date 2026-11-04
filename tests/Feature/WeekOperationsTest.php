<?php

declare(strict_types=1);

use App\Models\Dish;
use App\Models\DishNutrition;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\WeekPlan;
use App\Week\WeekBuilder;

// WEEK-001, Phần 2.5: thao tác tuần, mỗi thao tác một commit. File test này
// tăng dần theo từng thao tác; helper riêng (week25Dish) để không trùng
// helper week23Dish của WeekBuilderTest khi chạy cả thư mục.

function week25Dish(string $slug, string $slot, float $protein, int $kcalMin, int $kcalMax, array $opts = []): Dish
{
    $dish = Dish::create(['slug' => $slug, 'country_code' => 'VN', 'source' => 'curated', 'confidence' => 'low', 'completeness_level' => 1]);
    $dish->names()->create(['locale' => 'vi', 'name' => $slug, 'is_primary' => true, 'is_alias' => false]);
    $dish->mealSlots()->create(['slot' => $slot, 'weight' => 8]);
    foreach ($opts['ingredients'] ?? [] as $ingSlug) {
        $ing = \App\Models\Ingredient::where('slug', $ingSlug)->first()
            ?? \App\Models\Ingredient::create(['name_vi' => $ingSlug, 'slug' => $ingSlug, 'category' => $opts['category'] ?? 'khac', 'mapping_confidence' => 'high', 'source' => 'curated', 'confidence' => 'high']);
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

function week25Plan(): array
{
    $user = User::factory()->create(['role' => 'user']);
    UserProfile::create(['user_id' => $user->id, 'goal' => 'duy-tri', 'target_protein_g_per_day' => 100, 'target_kcal_per_day' => 2000]);
    week25Dish('op-sang', 'sang', 30, 450, 500);
    week25Dish('op-trua', 'trua', 30, 450, 500);
    week25Dish('op-trua-moi', 'trua', 40, 450, 500);
    week25Dish('op-toi', 'toi', 30, 450, 500);
    week25Dish('op-an-vat', 'an-vat', 20, 450, 500);
    $plan = app(WeekBuilder::class)->build($user, '2026-11-09');

    return [$user, $plan];
}

it('đổi một bữa: ứng viên hiển thị trước, áp dụng ghi món gốc và tính lại ngày', function () {
    [$user, $plan] = week25Plan();
    $this->actingAs($user);
    $meal = $plan->days->first()->meals->firstWhere('slot', 'trua');
    // Builder chọn món protein cao nhất (op-trua-moi, 40 g) cho ngày đầu,
    // nên đổi sang op-trua (30 g) để kiểm tra ghi món gốc + tính lại ngày.
    $newDish = Dish::where('slug', 'op-trua')->first();

    $this->getJson("/api/week-plans/{$plan->id}/meals/{$meal->id}/candidates")->assertOk()
        ->assertJsonFragment(['slug' => 'op-trua']);

    $resp = $this->postJson("/api/week-plans/{$plan->id}/meals/{$meal->id}/swap", ['dish_id' => $newDish->id])->assertOk();
    expect($resp->json('meal.dish.slug'))->toBe('op-trua');

    $meal->refresh();
    expect($meal->dish_id)->toBe($newDish->id)
        ->and($meal->replaced_from_dish_id)->not->toBeNull()
        ->and($resp->json('validation.protein_g'))->toBe(110);
});

it('đổi bữa sang món thiếu dữ kiện bị từ chối 422', function () {
    [$user, $plan] = week25Plan();
    $bad = week25Dish('op-trua-thieu', 'trua', 50, 450, 500, ['missing' => ['nghe-tuoi']]);
    $this->actingAs($user);
    $meal = $plan->days->first()->meals->firstWhere('slot', 'trua');

    $this->postJson("/api/week-plans/{$plan->id}/meals/{$meal->id}/swap", ['dish_id' => $bad->id])->assertUnprocessable();
});
