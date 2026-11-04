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

it('đổi ngày bắt đầu: dịch toàn bộ ngày, giữ nguyên món, trùng ngày khác thì 422', function () {
    [$user, $plan] = week25Plan();
    $this->actingAs($user);
    $firstMealSlug = $plan->days->first()->meals->first()->dish->slug;

    $resp = $this->postJson("/api/week-plans/{$plan->id}/change-start", ['start_date' => '2026-11-10'])->assertOk();
    expect($resp->json('plan.start_date'))->toBe('2026-11-10')
        ->and($resp->json('plan.end_date'))->toBe('2026-11-16');

    $fresh = WeekPlan::with('days.meals.dish')->find($plan->id);
    expect($fresh->days->first()->date->toDateString())->toBe('2026-11-10')
        ->and($fresh->days->first()->meals->first()->dish->slug)->toBe($firstMealSlug);

    WeekPlan::create(['user_id' => $user->id, 'start_date' => '2026-11-16', 'end_date' => '2026-11-22']);
    $this->postJson("/api/week-plans/{$plan->id}/change-start", ['start_date' => '2026-11-16'])->assertUnprocessable();
});

it('bắt đầu lại phần còn lại: ngày trước giữ nguyên, ngày từ mốc trở đi xây lại đủ 4 bữa', function () {
    [$user, $plan] = week25Plan();
    $this->actingAs($user);
    $before = WeekPlan::with('days.meals.dish')->find($plan->id);
    $keptSlugs = $before->days->first()->meals->map(fn ($m) => $m->dish->slug)->sort()->values()->all();

    $resp = $this->postJson("/api/week-plans/{$plan->id}/restart-remaining", ['from_date' => '2026-11-11'])->assertOk();
    expect($resp->json('rebuilt_day_ids'))->toHaveCount(5);

    $after = WeekPlan::with('days.meals.dish')->find($plan->id);
    expect($after->days->first()->meals->map(fn ($m) => $m->dish->slug)->sort()->values()->all())->toBe($keptSlugs)
        ->and($after->days->where('date', '>=', '2026-11-11')->first()->meals)->toHaveCount(4);

    $this->postJson("/api/week-plans/{$plan->id}/restart-remaining", ['from_date' => '2026-11-20'])->assertUnprocessable();
});

it('đánh dấu ăn ngoài: bữa không tính vào tổng, ngày thành chưa đủ dữ kiện; bỏ dấu thì trở lại đạt', function () {
    [$user, $plan] = week25Plan();
    $this->actingAs($user);
    $meal = $plan->days->first()->meals->firstWhere('slot', 'trua');
    expect($plan->days->first()->status)->toBe('dat');

    $resp = $this->postJson("/api/week-plans/{$plan->id}/meals/{$meal->id}/eat-out", ['is_eat_out' => true])->assertOk();
    expect($resp->json('meal.is_eat_out'))->toBeTrue()
        ->and($resp->json('validation.status'))->toBe('chua-du-du-lieu')
        ->and($resp->json('validation.unknown_meals'))->toBe(1);

    $resp = $this->postJson("/api/week-plans/{$plan->id}/meals/{$meal->id}/eat-out", ['is_eat_out' => false])->assertOk();
    expect($resp->json('validation.status'))->toBe('dat');
});

it('loại thực phẩm khỏi tuần: thêm vào hồ sơ, thay bữa chứa nó, bữa khác giữ nguyên', function () {
    [$user, $plan] = week25Plan();
    // Thêm món trua chứa lạc, protein cao hơn để builder chọn nó trước.
    week25Dish('op-trua-lac', 'trua', 60, 450, 500, ['ingredients' => ['lac-rang']]);
    // Xây lại tuần mới cho cùng người dùng với ngày khác để builder dùng món lạc.
    $plan2 = app(WeekBuilder::class)->build($user, '2026-11-16');
    $this->actingAs($user);
    $lacMeals = $plan2->meals()->whereHas('dish.dishIngredients.ingredient', fn ($q) => $q->where('slug', 'lac-rang'))->count();
    expect($lacMeals)->toBeGreaterThan(0);

    $resp = $this->postJson("/api/week-plans/{$plan2->id}/exclude-ingredient", ['ingredient_slug' => 'lac-rang'])->assertOk();
    expect($resp->json('replaced'))->toBeGreaterThan(0);

    $afterLac = $plan2->fresh()->meals()->whereHas('dish.dishIngredients.ingredient', fn ($q) => $q->where('slug', 'lac-rang'))->count();
    expect($afterLac)->toBe(0);
    expect(\App\Models\UserProfile::where('user_id', $user->id)->first()->excluded_ingredients)->toContain('lac-rang');
});

it('đổi bữa sang món thiếu dữ kiện bị từ chối 422', function () {
    [$user, $plan] = week25Plan();
    $bad = week25Dish('op-trua-thieu', 'trua', 50, 450, 500, ['missing' => ['nghe-tuoi']]);
    $this->actingAs($user);
    $meal = $plan->days->first()->meals->firstWhere('slot', 'trua');

    $this->postJson("/api/week-plans/{$plan->id}/meals/{$meal->id}/swap", ['dish_id' => $bad->id])->assertUnprocessable();
});
