<?php

declare(strict_types=1);

use App\Models\Dish;
use App\Models\DishNutrition;
use App\Models\Ingredient;
use App\Models\Tag;

/* UI-019: bộ lọc dưỡng chất + sắp xếp độ uy tín cho Thư viện/Nguyên liệu. */

function mkDish(string $slug, ?array $nutrition): Dish
{
    $dish = Dish::create(['slug' => $slug, 'country_code' => 'VN', 'source' => 'curated', 'confidence' => 'low', 'confidence' => 'low', 'completeness_level' => 1]);
    if ($nutrition !== null) {
        DishNutrition::create(['dish_id' => $dish->id, 'fct_source' => 'vn-fct-2017', 'basis' => 'per_serving', 'computed_at' => now(), 'engine_version' => '1.0.0', 'source' => 'computed', ...$nutrition]);
    }

    return $dish;
}

function baseNut(array $over = []): array
{
    return ['kcal_min' => 100, 'kcal_max' => 200, 'protein_g' => 5, 'fat_g' => 5, 'carb_g' => 20, 'fiber_g' => 1, 'confidence' => 'low', ...$over];
}

it('lọc dưỡng chất ở món: giàu đạm và ít calo áp theo ngưỡng khẩu phần', function () {
    mkDish('mon-giau-dam', baseNut(['protein_g' => 25]));
    mkDish('mon-it-dam', baseNut(['protein_g' => 3]));
    mkDish('mon-nhe', baseNut(['kcal_min' => 90, 'kcal_max' => 300]));
    mkDish('mon-nang', baseNut(['kcal_min' => 600, 'kcal_max' => 800]));

    $this->getJson('/api/dishes?nutrient=protein-high')->assertJsonPath('total', 1);
    $this->getJson('/api/dishes?nutrient=kcal-low')->assertJsonPath('total', 3);
    $this->getJson('/api/dishes?nutrient=protein-high,kcal-low')->assertJsonPath('total', 1);
});

it('sắp xếp độ uy tín: confidence cao trước, món chưa có số xuống cuối', function () {
    mkDish('mon-low', baseNut(['confidence' => 'low']));
    mkDish('mon-medium', baseNut(['confidence' => 'medium']));
    mkDish('mon-chua-co-so', null);

    $slugs = collect($this->getJson('/api/dishes?sort=confidence')->json('data'))->pluck('slug')->all();
    expect($slugs[0])->toBe('mon-medium')
        ->and($slugs[1])->toBe('mon-low')
        ->and($slugs[2])->toBe('mon-chua-co-so');
});

it('lọc dưỡng chất ở nguyên liệu: ít natri loại cả nguyên liệu chưa biết natri', function () {
    Ingredient::create(['slug' => 'nl-it-natri', 'name_vi' => 'NL ít natri', 'category' => 'rau-cu', 'mapping_confidence' => 'high', 'source' => 'curated', 'confidence' => 'low', 'nutrition_snapshot' => ['kcal_per_100g' => 50, 'sodium_mg' => 50]]);
    Ingredient::create(['slug' => 'nl-nhieu-natri', 'name_vi' => 'NL nhiều natri', 'category' => 'rau-cu', 'mapping_confidence' => 'high', 'source' => 'curated', 'confidence' => 'low', 'nutrition_snapshot' => ['kcal_per_100g' => 50, 'sodium_mg' => 900]]);
    Ingredient::create(['slug' => 'nl-chua-biet', 'name_vi' => 'NL chưa biết', 'category' => 'rau-cu', 'mapping_confidence' => 'high', 'source' => 'curated', 'confidence' => 'low', 'nutrition_snapshot' => ['kcal_per_100g' => 50, 'sodium_mg' => null]]);

    $slugs = collect($this->getJson('/api/ingredients?nutrient=sodium-low')->json('data'))->pluck('slug')->all();
    expect($slugs)->toBe(['nl-it-natri']);
});
