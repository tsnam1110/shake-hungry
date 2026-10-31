<?php

declare(strict_types=1);

use App\Models\Dish;
use App\Models\DishNutrition;
use App\Models\Fruit;
use App\Models\Ingredient;
use App\Models\Tag;
use App\Models\User;

/*
 * ADM-008: tổng quan quản trị có đủ số lượng bản ghi + chỉ số chất lượng;
 * các bộ lọc mới ở danh sách nguyên liệu / món / quả lọc đúng.
 */

function statsAdmin(): User
{
    return User::factory()->create(['role' => 'admin']);
}

function statsIngredient(array $overrides = []): Ingredient
{
    return Ingredient::create(array_merge([
        'name_vi' => 'Nguyên liệu '.uniqid(),
        'name_en' => 'Ingredient',
        'slug' => 'nl-'.uniqid(),
        'category' => 'rau-cu',
        'mapping_confidence' => 'high',
        'source' => 'curated',
        'confidence' => 'high',
        'needs_review' => false,
        'reviewed_by' => 'tester@lacdoi.local',
        'reviewed_at' => '2026-10-31',
        'nutrition_snapshot' => ['kcal_per_100g' => 20.0, 'fct_source' => 'usda_sr_legacy'],
    ], $overrides));
}

function statsDish(array $overrides = []): Dish
{
    return Dish::create(array_merge([
        'slug' => 'mon-'.uniqid(),
        'country_code' => 'VN',
        'cooking_method' => 'nau',
        'completeness_level' => 1,
        'is_eat_out' => true,
        'is_home_cook' => true,
        'source' => 'curated',
        'confidence' => 'medium',
        'needs_review' => false,
        'reviewed_by' => 'tester@lacdoi.local',
        'reviewed_at' => '2026-10-31',
    ], $overrides));
}

test('tổng quan trả đủ số lượng bản ghi và chỉ số chất lượng', function () {
    $this->actingAs(statsAdmin());
    statsIngredient();
    statsIngredient(['nutrition_snapshot' => ['kcal_per_100g' => null, 'fct_source' => 'mext_jp'], 'needs_review' => true]);
    $dishOk = statsDish();
    DishNutrition::create(['dish_id' => $dishOk->id, 'basis' => 'per_serving', 'confidence' => 'medium', 'fct_source' => 'usda_sr_legacy', 'source' => 'engine', 'engine_version' => '1.1.0', 'computed_at' => now(), 'kcal_min' => 100, 'kcal_max' => 120, 'unverified_ingredients' => ['x']]);
    statsDish(['needs_review' => true, 'reviewed_by' => null, 'reviewed_at' => null]);
    $fruitIngredient = statsIngredient(['category' => 'trai-cay']);
    $fruit = Fruit::create([
        'slug' => $fruitIngredient->slug, 'ingredient_id' => $fruitIngredient->id,
        'classification_group' => 'mong', 'origin' => 'nhap-khau',
        'availability_months' => [1, 2], 'availability_type' => 'quanh-nam-nhap-khau',
        'needs_review' => true,
    ]);
    $fruit->prices()->create([
        'region_code' => 'bac', 'month' => 1, 'price_min' => 10000, 'price_max' => 20000,
        'source_name' => 'Nguồn mẫu', 'collected_at' => '2026-10-31',
    ]);

    $this->getJson('/admin/api/dashboard')
        ->assertOk()
        ->assertJsonPath('ingredients.total', 3)
        ->assertJsonPath('ingredients.pending_review', 1)
        ->assertJsonPath('ingredients.missing_nutrition', 1)
        ->assertJsonPath('ingredients.unverified', 1)
        ->assertJsonPath('dishes.total', 2)
        ->assertJsonPath('dishes.computable', 1)
        ->assertJsonPath('dishes.missing_nutrition', 1)
        ->assertJsonPath('dishes.unverified', 1)
        ->assertJsonPath('fruits.total', 1)
        ->assertJsonPath('fruits.with_prices', 1)
        ->assertJsonPath('fruits.price_rows', 1);
});

test('lọc nguyên liệu theo độ tin cậy ánh xạ và số dinh dưỡng', function () {
    $this->actingAs(statsAdmin());
    statsIngredient(['mapping_confidence' => 'high']);
    statsIngredient(['mapping_confidence' => 'low', 'mapping_note' => 'Ánh xạ gần đúng, chờ kiểm chứng.', 'nutrition_snapshot' => ['kcal_per_100g' => null]]);

    $this->getJson('/admin/api/ingredients?mapping_confidence=low')
        ->assertOk()->assertJsonPath('total', 1);
    $this->getJson('/admin/api/ingredients?nutrition=missing')
        ->assertOk()->assertJsonPath('total', 1);
    $this->getJson('/admin/api/ingredients?nutrition=has')
        ->assertOk()->assertJsonPath('total', 1);
});

test('lọc món theo vùng, chế độ ăn và dinh dưỡng', function () {
    $this->actingAs(statsAdmin());
    $north = \App\Models\Region::create(['country_code' => 'VN', 'code' => 'vn-north', 'name' => 'Miền Bắc', 'level' => 'macro_region']);
    $south = \App\Models\Region::create(['country_code' => 'VN', 'code' => 'vn-south', 'name' => 'Miền Nam', 'level' => 'macro_region']);
    $chay = statsDish(['region_id' => $north->id]);
    $chay->tags()->attach(Tag::create(['slug' => 'diet:chay', 'name' => 'Chay']));
    DishNutrition::create(['dish_id' => $chay->id, 'basis' => 'per_serving', 'confidence' => 'medium', 'fct_source' => 'usda_sr_legacy', 'source' => 'engine', 'engine_version' => '1.1.0', 'computed_at' => now(), 'kcal_min' => 50, 'kcal_max' => 60]);
    statsDish(['region_id' => $south->id]);

    $this->getJson('/admin/api/dishes?region_id='.$south->id)
        ->assertOk()->assertJsonPath('total', 1);
    $this->getJson('/admin/api/dishes?diet=chay')
        ->assertOk()->assertJsonPath('total', 1);
    $this->getJson('/admin/api/dishes?nutrition=missing')
        ->assertOk()->assertJsonPath('total', 1);
    $this->getJson('/admin/api/dishes?cooking_method=nau')
        ->assertOk()->assertJsonPath('total', 2);
});

test('lọc hồ sơ quả theo nguồn gốc và tình trạng giá', function () {
    $this->actingAs(statsAdmin());
    $ingA = statsIngredient(['category' => 'trai-cay']);
    $fruitA = Fruit::create([
        'slug' => $ingA->slug, 'ingredient_id' => $ingA->id,
        'classification_group' => 'mong', 'origin' => 'nhap-khau',
        'availability_months' => [1], 'availability_type' => 'quanh-nam-nhap-khau', 'needs_review' => false,
    ]);
    $fruitA->prices()->create([
        'region_code' => 'nam', 'month' => 1, 'price_min' => 5000, 'price_max' => 9000,
        'source_name' => 'Nguồn mẫu', 'collected_at' => '2026-10-31',
    ]);
    $ingB = statsIngredient(['category' => 'trai-cay']);
    Fruit::create([
        'slug' => $ingB->slug, 'ingredient_id' => $ingB->id,
        'classification_group' => 'dua', 'origin' => 'trong-nuoc',
        'availability_months' => [6], 'availability_type' => 'theo-mua', 'needs_review' => false,
    ]);

    $this->getJson('/admin/api/fruits?origin=trong-nuoc')
        ->assertOk()->assertJsonPath('total', 1);
    $this->getJson('/admin/api/fruits?prices=none')
        ->assertOk()->assertJsonPath('total', 1);
    $this->getJson('/admin/api/fruits?prices=has')
        ->assertOk()->assertJsonPath('total', 1);
});

test('lọc đóng góp cộng đồng theo loại', function () {
    $this->actingAs(statsAdmin());
    \App\Models\Contribution::create(['type' => 'new_dish', 'payload' => ['detail' => 'Món mới A'], 'status' => 'pending']);
    \App\Models\Contribution::create(['type' => 'fix_dish', 'payload' => ['detail' => 'Sửa món B'], 'status' => 'pending']);

    $this->getJson('/admin/api/contributions?type=new_dish')
        ->assertOk()->assertJsonPath('total', 1);
    $this->getJson('/admin/api/contributions?status=pending')
        ->assertOk()->assertJsonPath('total', 2);
});
