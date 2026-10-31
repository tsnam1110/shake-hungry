<?php

declare(strict_types=1);

use App\Models\Dish;
use App\Models\DishIngredient;
use App\Models\DishNutrition;
use App\Models\Ingredient;
use App\Nutrition\DishNutritionService;

/**
 * Cờ "chưa kiểm định" cho nguồn thứ cấp (quyết định chủ dự án 2026-10-31).
 *
 * Nguyên liệu lấy từ nguồn thứ cấp (Bảng TPTP VN, văn liệu...) chỉ được dùng khi
 * nguồn chính thống không có, và con số của món dùng chúng PHẢI bị đánh dấu để
 * người dùng biết "đã có nhưng chưa được kiểm định". Test này khoá hành vi:
 * đánh dấu đúng nguyên liệu thứ cấp đã tham gia tính, không đánh dấu oan nguyên
 * liệu chính thống, và nguyên liệu thứ cấp KHÔNG tham gia tính (thiếu số) thì
 * không bị tính là đã dùng.
 */
function unverifiedIngredient(string $fct, ?float $kcal): Ingredient
{
    return Ingredient::create([
        'slug' => 'test-unv-'.uniqid(),
        'name_vi' => 'Nguyên liệu test '.$fct,
        'mapping_confidence' => 'low',
        'mapping_note' => 'Số giả lập phục vụ test cờ chưa kiểm định (R7: confidence khác high bắt buộc có ghi chú).',
        'category' => 'khac',
        'nutrition_snapshot' => [
            'fct_source' => $fct,
            'external_id' => '0',
            'kcal_per_100g' => $kcal,
            'protein_g' => 10.0,
            'fat_g' => 5.0,
            'carb_g' => 20.0,
        ],
        'source' => 'curated',
        'confidence' => 'low',
    ]);
}

function unverifiedDish(array $ingredients): Dish
{
    $dish = Dish::create([
        'slug' => 'test-unv-mon-'.uniqid(),
        'country_code' => 'VN',
        'completeness_level' => 1,
        'cooking_method' => 'kho',
        'source' => 'curated',
        'confidence' => 'medium',
    ]);
    foreach ($ingredients as $item) {
        [$ing, $grams] = is_array($item) ? $item : [$item, 100];
        DishIngredient::create([
            'dish_id' => $dish->id, 'ingredient_id' => $ing->id,
            'quantity' => $grams, 'unit_name' => 'g', 'source' => 'curated',
        ]);
    }

    return $dish;
}

it('danh dau mon co nguyen lieu nguon thu cap la chua kiem dinh', function () {
    $dish = unverifiedDish([
        unverifiedIngredient('usda_sr_legacy', 150.0),
        $secondary = unverifiedIngredient('vn_nin', 90.0),
    ]);

    $nutrition = app(DishNutritionService::class)->computeAndStore($dish, 'mixed');

    expect($nutrition)->not->toBeNull()
        ->and($nutrition->unverified_ingredients)->toBe([$secondary->slug]);
});

it('khong danh dau mon chi dung nguon chinh thong', function () {
    $dish = unverifiedDish([
        unverifiedIngredient('usda_sr_legacy', 150.0),
        unverifiedIngredient('nhan_san_pham', 90.0),
    ]);

    $nutrition = app(DishNutritionService::class)->computeAndStore($dish, 'mixed');

    expect($nutrition)->not->toBeNull()
        ->and($nutrition->unverified_ingredients ?? [])->toBe([]);
});

it('nguyen lieu thu cap thieu so (khong tham gia tinh) thi khong bi danh dau', function () {
    // Nguyên liệu thứ cấp thiếu số chỉ chiếm 10g/210g — dưới ngưỡng 30% khối lượng
    // thiếu nên món vẫn tính được, và nó phải nằm ở missing[] chứ không phải unverified[].
    $dish = unverifiedDish([
        [unverifiedIngredient('usda_sr_legacy', 150.0), 200],
        [unverifiedIngredient('vn_nin', null), 10],
    ]);

    $nutrition = app(DishNutritionService::class)->computeAndStore($dish, 'mixed');

    expect($nutrition)->not->toBeNull()
        ->and($nutrition->missing_ingredients)->not->toBeEmpty()
        ->and($nutrition->unverified_ingredients ?? [])->toBe([]);
});

it('api chi tiet mon tra ten nguyen lieu chua kiem dinh trong provenance', function () {
    $dish = unverifiedDish([
        unverifiedIngredient('usda_sr_legacy', 150.0),
        unverifiedIngredient('vn_nin', 90.0),
    ]);
    app(DishNutritionService::class)->computeAndStore($dish, 'mixed');

    $response = $this->getJson('/api/dishes/'.$dish->slug);

    $response->assertOk()
        ->assertJsonPath('provenance.unverified_ingredients', ['Nguyên liệu test vn_nin']);
    expect(DishNutrition::where('dish_id', $dish->id)->first()?->unverified_ingredients)
        ->not->toBeEmpty();
});
