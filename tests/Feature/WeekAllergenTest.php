<?php

declare(strict_types=1);

use App\Models\Dish;
use App\Models\DishNutrition;
use App\Models\Ingredient;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\WeekPlan;
use App\Models\WeekPlanDay;
use App\Models\WeekPlanMeal;

// Giai đoạn 3, Task 1: nhãn dị ứng món trong tuần ăn (hiển thị, không
// thay lọc cứng — lọc vẫn qua excluded_ingredients ở builder/swap).

it('chi tiết tuần trả nhãn dị ứng của món, kể cả nhóm đậu phộng', function () {
    $user = User::factory()->create(['role' => 'user']);
    UserProfile::create(['user_id' => $user->id]);
    $ing = Ingredient::create(['name_vi' => 'lạc rang', 'slug' => 'lac-rang-test', 'category' => 'khac', 'mapping_confidence' => 'high', 'source' => 'curated', 'confidence' => 'high']);
    $ing->tags()->attach(Tag::firstOrCreate(['slug' => 'allergen:dau-phong'], ['name' => 'Đậu phộng'])->id);
    $dish = Dish::create(['slug' => 'mon-lac-test', 'country_code' => 'VN', 'source' => 'curated', 'confidence' => 'low', 'completeness_level' => 1]);
    $dish->names()->create(['locale' => 'vi', 'name' => 'Món lạc', 'is_primary' => true, 'is_alias' => false]);
    $dish->dishIngredients()->create(['ingredient_id' => $ing->id, 'quantity' => 100, 'unit_name' => 'g', 'source' => 'curated']);
    DishNutrition::create(['dish_id' => $dish->id, 'basis' => 'per_serving', 'confidence' => 'medium', 'fct_source' => 'usda_sr_legacy', 'computed_at' => now(), 'engine_version' => '1.1.0', 'source' => 'computed', 'kcal_min' => 400, 'kcal_max' => 500, 'protein_g' => 20, 'missing_ingredients' => [], 'unverified_ingredients' => []]);

    $plan = WeekPlan::create(['user_id' => $user->id, 'start_date' => '2026-11-09', 'end_date' => '2026-11-15']);
    $day = WeekPlanDay::create(['week_plan_id' => $plan->id, 'date' => '2026-11-09']);
    WeekPlanMeal::create(['week_plan_day_id' => $day->id, 'dish_id' => $dish->id, 'slot' => 'trua']);

    $this->actingAs($user);
    $this->getJson("/api/week-plans/{$plan->id}")->assertOk()
        ->assertJsonFragment(['group' => 'dau-phong', 'label' => 'Đậu phộng', 'certainty' => 'definite']);
});
