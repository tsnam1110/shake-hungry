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

// WEEK-001, Phần 2.6: danh sách đi chợ theo gram, không gói, không giá.
// Bữa ăn ngoài không tính; dòng không có grams_resolved giữ nguyên đơn vị.

it('đi chợ cộng gộp gram theo nguyên liệu, loại bữa ăn ngoài, giữ đơn vị khi không có gram', function () {
    $user = User::factory()->create(['role' => 'user']);
    UserProfile::create(['user_id' => $user->id, 'target_protein_g_per_day' => 100, 'target_kcal_per_day' => 2000]);
    $ga = Ingredient::create(['name_vi' => 'thịt gà', 'slug' => 'thit-ga-test', 'category' => 'thit', 'mapping_confidence' => 'high', 'source' => 'curated', 'confidence' => 'high']);
    $trung = Ingredient::create(['name_vi' => 'trứng gà', 'slug' => 'trung-ga-test', 'category' => 'khac', 'mapping_confidence' => 'high', 'source' => 'curated', 'confidence' => 'high']);

    $makeDish = function (string $slug, array $ings) {
        $dish = Dish::create(['slug' => $slug, 'country_code' => 'VN', 'source' => 'curated', 'confidence' => 'low', 'completeness_level' => 1]);
        $dish->names()->create(['locale' => 'vi', 'name' => $slug, 'is_primary' => true, 'is_alias' => false]);
        $dish->mealSlots()->create(['slot' => 'trua', 'weight' => 8]);
        foreach ($ings as [$ing, $grams, $qty, $unit]) {
            $dish->dishIngredients()->create(['ingredient_id' => $ing->id, 'quantity' => $qty, 'unit_name' => $unit, 'grams_resolved' => $grams, 'source' => 'curated']);
        }
        DishNutrition::create(['dish_id' => $dish->id, 'basis' => 'per_serving', 'confidence' => 'medium', 'fct_source' => 'usda_sr_legacy', 'computed_at' => now(), 'engine_version' => '1.1.0', 'source' => 'computed', 'kcal_min' => 400, 'kcal_max' => 500, 'protein_g' => 30, 'missing_ingredients' => [], 'unverified_ingredients' => []]);

        return $dish;
    };
    $d1 = $makeDish('cho-ga-1', [[$ga, 200, 200, 'g'], [$trung, null, 2, 'quả']]);
    $d2 = $makeDish('cho-ga-2', [[$ga, 100, 100, 'g']]);

    $plan = WeekPlan::create(['user_id' => $user->id, 'start_date' => '2026-11-09', 'end_date' => '2026-11-15']);
    $day = WeekPlanDay::create(['week_plan_id' => $plan->id, 'date' => '2026-11-09']);
    WeekPlanMeal::create(['week_plan_day_id' => $day->id, 'dish_id' => $d1->id, 'slot' => 'trua']);
    WeekPlanMeal::create(['week_plan_day_id' => $day->id, 'dish_id' => $d2->id, 'slot' => 'toi']);
    // Bữa ăn ngoài chứa gà nhưng không tính vào đi chợ.
    $day2 = WeekPlanDay::create(['week_plan_id' => $plan->id, 'date' => '2026-11-10']);
    WeekPlanMeal::create(['week_plan_day_id' => $day2->id, 'dish_id' => $d1->id, 'slot' => 'trua', 'is_eat_out' => true]);

    $this->actingAs($user);
    $resp = $this->getJson("/api/week-plans/{$plan->id}/shopping-list")->assertOk();
    $items = collect($resp->json('items'));
    $gaItem = $items->firstWhere('slug', 'thit-ga-test');
    $trungItem = $items->firstWhere('slug', 'trung-ga-test');

    expect($gaItem['total_grams'])->toBe(300)
        ->and($trungItem['total_grams'])->toBeNull()
        ->and($trungItem['unit_entries'][0]['quantity'])->toBe(2)
        ->and($trungItem['unit_entries'][0]['unit_name'])->toBe('quả')
        ->and($resp->json('note'))->toContain('snapshot giá 04/11');
});

it('đi chợ nhóm theo gian hàng chung, thịt vào quầy thịt · thủy sản', function () {
    $user = User::factory()->create(['role' => 'user']);
    UserProfile::create(['user_id' => $user->id]);
    $ga = Ingredient::create(['name_vi' => 'thịt gà', 'slug' => 'thit-ga-aisle', 'category' => 'thit', 'mapping_confidence' => 'high', 'source' => 'curated', 'confidence' => 'high']);
    $dish = Dish::create(['slug' => 'mon-ga-aisle', 'country_code' => 'VN', 'source' => 'curated', 'confidence' => 'low', 'completeness_level' => 1]);
    $dish->dishIngredients()->create(['ingredient_id' => $ga->id, 'quantity' => 100, 'unit_name' => 'g', 'grams_resolved' => 100, 'source' => 'curated']);
    $plan = WeekPlan::create(['user_id' => $user->id, 'start_date' => '2026-11-09', 'end_date' => '2026-11-15']);
    $day = WeekPlanDay::create(['week_plan_id' => $plan->id, 'date' => '2026-11-09']);
    WeekPlanMeal::create(['week_plan_day_id' => $day->id, 'dish_id' => $dish->id, 'slot' => 'trua']);

    $this->actingAs($user);
    $this->getJson("/api/week-plans/{$plan->id}/shopping-list")->assertOk()
        ->assertJsonFragment(['aisle' => 'Quầy thịt · thủy sản'])
        ->assertJsonFragment(['slug' => 'thit-ga-aisle']);
});

// F1: quy đổi gói từ snapshot 04/11 — nhiều gói thì hiện nhiều gói,
// số gói làm tròn lên, ánh xạ ứng viên chưa hiển thị.
it('F1 quy đổi gói khối lượng, dung tích qua density, và gói quả từ unit', function () {
    $duong = \App\Week\PackMapper::optionsFor('duong-trang', 1200, 0.845, []);
    $goi500 = collect($duong)->firstWhere('pack_grams', 500.0);
    $goi1kg = collect($duong)->firstWhere('pack_grams', 1000.0);
    expect($goi500['packs_needed'])->toBe(3)
        ->and($goi1kg['packs_needed'])->toBe(2)
        ->and(collect($duong)->every(fn ($o) => $o['confidence'] === 'khớp'))->toBeTrue()
        ->and(\App\Week\PackMapper::unverifiedCount('duong-trang'))->toBe(1);

    // 900 g dầu ăn / density 0,92 ≈ 978 ml → chai 1 L cần 1.
    $dau = \App\Week\PackMapper::optionsFor('dau-an', 900, 0.92, []);
    $chai1l = collect($dau)->firstWhere('pack_ml', 1000.0);
    expect($chai1l['packs_needed'])->toBe(1);

    // 12 quả từ unit_entries → hộp 10 quả cần 2 hộp.
    $trung = \App\Week\PackMapper::optionsFor('trung-ga', null, null, [['unit_name' => 'quả', 'quantity' => 12]]);
    $hop10 = collect($trung)->firstWhere('pack_count', 10);
    expect($hop10['packs_needed'])->toBe(2);

    // Toàn ứng viên (gạo tẻ) thì chưa hiển thị gói nào, chỉ đếm.
    expect(\App\Week\PackMapper::optionsFor('gao-te', 1000, 0.782, []))->toBe([])
        ->and(\App\Week\PackMapper::unverifiedCount('gao-te'))->toBe(23);
});

it('F1 đi chợ qua API gắn lựa chọn gói cho nguyên liệu có ánh xạ khớp', function () {
    $user = User::factory()->create(['role' => 'user']);
    UserProfile::create(['user_id' => $user->id, 'target_protein_g_per_day' => 100, 'target_kcal_per_day' => 2000]);
    $duong = Ingredient::create(['name_vi' => 'đường trắng', 'slug' => 'duong-trang', 'category' => 'gia-vi', 'mapping_confidence' => 'high', 'source' => 'curated', 'confidence' => 'high', 'density_g_per_ml' => 0.845, 'density_note' => 'test']);
    $dish = Dish::create(['slug' => 'mon-duong-f1', 'country_code' => 'VN', 'source' => 'curated', 'confidence' => 'low', 'completeness_level' => 1]);
    $dish->names()->create(['locale' => 'vi', 'name' => 'mon-duong-f1', 'is_primary' => true, 'is_alias' => false]);
    $dish->mealSlots()->create(['slot' => 'trua', 'weight' => 8]);
    $dish->dishIngredients()->create(['ingredient_id' => $duong->id, 'quantity' => 1200, 'unit_name' => 'g', 'grams_resolved' => 1200, 'source' => 'curated']);
    DishNutrition::create(['dish_id' => $dish->id, 'basis' => 'per_serving', 'confidence' => 'medium', 'fct_source' => 'usda_sr_legacy', 'computed_at' => now(), 'engine_version' => '1.1.0', 'source' => 'computed', 'kcal_min' => 400, 'kcal_max' => 500, 'protein_g' => 30, 'missing_ingredients' => [], 'unverified_ingredients' => []]);
    $plan = WeekPlan::create(['user_id' => $user->id, 'start_date' => '2026-11-09', 'end_date' => '2026-11-15']);
    $day = WeekPlanDay::create(['week_plan_id' => $plan->id, 'date' => '2026-11-09']);
    WeekPlanMeal::create(['week_plan_day_id' => $day->id, 'dish_id' => $dish->id, 'slot' => 'trua']);

    $this->actingAs($user);
    $resp = $this->getJson("/api/week-plans/{$plan->id}/shopping-list")->assertOk();
    $item = collect($resp->json('items'))->firstWhere('slug', 'duong-trang');
    expect($item['total_grams'])->toBe(1200)
        ->and($item['pack_options'])->not->toBeEmpty()
        ->and(collect($item['pack_options'])->every(fn ($o) => $o['snapshot_date'] === '2026-11-04'))->toBeTrue()
        ->and(collect($item['pack_options'])->firstWhere('pack_grams', 500.0)['packs_needed'])->toBe(3);
});
