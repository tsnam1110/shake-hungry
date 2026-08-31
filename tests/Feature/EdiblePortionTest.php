<?php

declare(strict_types=1);

use App\Models\Dish;
use App\Models\DishIngredient;
use App\Models\Ingredient;
use App\Nutrition\DishNutritionService;

/**
 * DATA-008 — hệ số phần ăn được áp TRƯỚC khi tra dinh dưỡng (CALORIE-ENGINE §3b).
 * "300 g sườn" là khối lượng mua ở chợ (cả xương); áp thẳng kcal/100g của phần
 * ăn được lên 300 g là vống calo gần gấp đôi — lỗi âm thầm lan rộng.
 */
function suonTest(?float $ediblePct): Ingredient
{
    return Ingredient::create([
        'slug' => 'test-suon-'.uniqid(),
        'name_vi' => 'Sườn test',
        'mapping_confidence' => 'high',
        'category' => 'thit',
        'edible_portion_pct' => $ediblePct,
        'edible_portion_note' => $ediblePct !== null ? 'giá trị giả cho test' : null,
        'nutrition_snapshot' => [
            'fct_source' => 'usda_sr_legacy',
            'external_id' => '0',
            'kcal_per_100g' => 200.0,
        ],
        'source' => 'curated',
        'confidence' => 'high',
    ]);
}

function dishWith300g(Ingredient $ing): Dish
{
    $dish = Dish::create([
        'slug' => 'test-mon-'.uniqid(),
        'country_code' => 'VN',
        'completeness_level' => 1,
        'cooking_method' => 'kho',
        'source' => 'curated',
        'confidence' => 'medium',
    ]);
    DishIngredient::create([
        'dish_id' => $dish->id, 'ingredient_id' => $ing->id,
        'quantity' => 300, 'unit_name' => 'g', 'source' => 'curated',
    ]);

    return $dish;
}

it('ap he so phan an duoc truoc khi tra dinh duong — 300g suon thap hon ro ret', function () {
    $service = app(DishNutritionService::class);

    $khongHeSo = $service->compute(dishWith300g(suonTest(null)), 'usda_sr_legacy', portionWeighed: true);
    $coHeSo = $service->compute(dishWith300g(suonTest(50.0)), 'usda_sr_legacy', portionWeighed: true);

    // 300 g × 200 kcal/100g = 600 so với 150 g ăn được × 200 = 300
    expect($khongHeSo->kcalMax)->toBe(600.0)
        ->and($coHeSo->kcalMax)->toBe(300.0)
        ->and($coHeSo->kcalMax)->toBeLessThan($khongHeSo->kcalMin);
});

it('is_edible_weight=true thi khong ap he so nua — quantity da la phan an duoc', function () {
    $service = app(DishNutritionService::class);

    $dish = dishWith300g(suonTest(50.0));
    $dish->dishIngredients()->update(['is_edible_weight' => true]);
    $result = $service->compute($dish->fresh(), 'usda_sr_legacy', portionWeighed: true);

    expect($result->kcalMax)->toBe(600.0);
});
