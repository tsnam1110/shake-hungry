<?php

declare(strict_types=1);

use App\Models\Fruit;
use App\Models\Ingredient;

/* UI-024: tab Hoa quả trong Thư viện — toàn danh sách + lọc nâng cao. */

function mkFruit(string $slug, array $snapshot): Fruit
{
    $ing = Ingredient::create([
        'slug' => 'nl-'.$slug, 'name_vi' => 'Quả '.$slug, 'category' => 'trai-cay',
        'mapping_confidence' => 'high', 'source' => 'curated', 'confidence' => 'low',
        'nutrition_snapshot' => $snapshot,
    ]);

    return Fruit::create(['slug' => $slug, 'ingredient_id' => $ing->id, 'classification_group' => 'khac', 'origin' => 'trong-nuoc', 'availability_months' => range(1, 12), 'main_season_months' => [6], 'best_value_months' => [6], 'best_nutrition_months' => [6], 'availability_type' => 'quanh-nam']);
}

it('lọc dưỡng chất hoa quả theo ngưỡng 100g và loại giá trị chưa biết ở lọc thấp', function () {
    mkFruit('qua-nhe', ['kcal_per_100g' => 40, 'fiber_g' => 4, 'carb_g' => 8, 'water_g' => 90]);
    mkFruit('qua-nang', ['kcal_per_100g' => 300, 'fiber_g' => 1, 'carb_g' => 60, 'water_g' => 40]);
    mkFruit('qua-chua-biet', ['kcal_per_100g' => null, 'fiber_g' => null, 'carb_g' => null, 'water_g' => null]);

    $this->getJson('/api/fruits?nutrient=kcal-low')->assertJsonPath('total', 1);
    $this->getJson('/api/fruits?nutrient=fiber-high')->assertJsonPath('total', 1);
    $this->getJson('/api/fruits?nutrient=kcal-low,fiber-high')->assertJsonPath('total', 1);
    $this->getJson('/api/fruits?nutrient=water-high')->assertJsonPath('total', 1);
});

it('sắp xếp kcal tăng dần và per_page mở toàn danh sách', function () {
    mkFruit('qua-100', ['kcal_per_100g' => 100]);
    mkFruit('qua-50', ['kcal_per_100g' => 50]);

    $names = collect($this->getJson('/api/fruits?sort=kcal_asc')->json('data'))->pluck('slug')->all();
    expect($names[0])->toBe('qua-50');
    $this->getJson('/api/fruits?per_page=100')->assertJsonPath('total', 2);
});
