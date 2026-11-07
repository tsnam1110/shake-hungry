<?php

declare(strict_types=1);

use App\Combinations\CombinationDisplay;
use App\Models\Combination;
use App\Models\Dish;
use App\Models\DishNutrition;
use App\Models\Ingredient;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\WeekPlan;
use App\Models\WeekPlanDay;
use App\Models\WeekPlanMeal;

// Tiếp tục 2026-11-04: cặp kết hợp cần lưu ý trong chi tiết tuần — chỉ
// hiển thị (nguyên tắc đỏ §7.5), không lọc, không chặn, không chấm điểm.
// Cặp thuốc × thực phẩm không bao giờ vào món, nên không kiểm ở đây.

function weekCombDish(string $slug, string $slot, array $ingredients): Dish
{
    $dish = Dish::create(['slug' => $slug, 'country_code' => 'VN', 'source' => 'curated', 'confidence' => 'low', 'completeness_level' => 1]);
    $dish->names()->create(['locale' => 'vi', 'name' => $slug, 'is_primary' => true, 'is_alias' => false]);
    $dish->mealSlots()->create(['slot' => $slot, 'weight' => 8]);
    foreach ($ingredients as $ing) {
        $dish->dishIngredients()->create(['ingredient_id' => $ing->id, 'quantity' => 100, 'unit_name' => 'g', 'grams_resolved' => 100, 'source' => 'curated']);
    }
    DishNutrition::create(['dish_id' => $dish->id, 'basis' => 'per_serving', 'confidence' => 'medium', 'fct_source' => 'usda_sr_legacy', 'computed_at' => now(), 'engine_version' => '1.1.0', 'source' => 'computed', 'kcal_min' => 400, 'kcal_max' => 500, 'protein_g' => 30, 'missing_ingredients' => [], 'unverified_ingredients' => []]);

    return $dish;
}

it('chi tiết tuần trả cặp kết hợp của món, kèm miễn trừ', function () {
    $user = User::factory()->create(['role' => 'user']);
    UserProfile::create(['user_id' => $user->id, 'target_protein_g_per_day' => 100, 'target_kcal_per_day' => 2000]);

    $a = Ingredient::create(['slug' => 'wc-tom', 'name_vi' => 'Tôm', 'category' => 'thuy-san', 'source' => 'curated', 'mapping_confidence' => 'high', 'confidence' => 'high']);
    $b = Ingredient::create(['slug' => 'wc-vit', 'name_vi' => 'Thịt vịt', 'category' => 'thit', 'source' => 'curated', 'mapping_confidence' => 'high', 'confidence' => 'high']);
    $dish = weekCombDish('wc-mon', 'sang', [$a, $b]);

    Combination::create([
        'slug' => 'wc-tom-vit', 'side_a_type' => 'ingredient', 'side_a_ref' => 'wc-tom',
        'side_b_type' => 'ingredient', 'side_b_ref' => 'wc-vit', 'category' => 'food_food',
        'evidence_class' => 'E2', 'science_status' => 'conditional', 'sources' => [],
        'condition_vi' => 'Không nên ăn chung theo dân gian',
    ]);

    $plan = WeekPlan::create(['user_id' => $user->id, 'start_date' => '2026-11-09', 'end_date' => '2026-11-15', 'target_protein_g_per_day' => 100, 'target_kcal_per_day' => 2000, 'status' => 'active']);
    $day = WeekPlanDay::create(['week_plan_id' => $plan->id, 'date' => '2026-11-09', 'protein_g' => 30, 'kcal_min' => 400, 'kcal_max' => 500, 'status' => 'chua-du-du-lieu']);
    WeekPlanMeal::create(['week_plan_day_id' => $day->id, 'dish_id' => $dish->id, 'slot' => 'sang']);

    $this->actingAs($user)->getJson('/api/week-plans/'.$plan->id)->assertOk()
        ->assertJsonPath('combinations_disclaimer', CombinationDisplay::DISCLAIMER)
        ->assertJsonPath('days.0.meals.0.dish.combinations.notes.0.slug', 'wc-tom-vit');
});
