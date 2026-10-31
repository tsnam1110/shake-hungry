<?php

declare(strict_types=1);

use App\Models\Ingredient;

/* UI-017: trang danh sách + chi tiết nguyên liệu phía người dùng. */

function ingFixture(string $slug, ?float $kcal): Ingredient
{
    return Ingredient::create([
        'name_vi' => $slug, 'slug' => $slug, 'category' => 'rau-cu',
        'mapping_confidence' => 'high', 'source' => 'curated', 'confidence' => 'high',
        'nutrition_snapshot' => $kcal === null ? ['fct_source' => null] : ['kcal_per_100g' => $kcal, 'kcal_min' => $kcal, 'kcal_max' => $kcal, 'fct_source' => 'usda_sr_legacy'],
    ]);
}

it('trang danh sách và chi tiết nguyên liệu mở được', function () {
    $this->get('/nguyen-lieu')->assertOk()->assertSee('Tra cứu toàn bộ nguyên liệu');
    $this->get('/nguyen-lieu/bat-ky')->assertOk();
});

it('API chi tiết nguyên liệu trả dinh dưỡng, quả liên kết và món dùng nó', function () {
    $ing = ingFixture('nguyen-lieu-test', 42);
    $this->getJson('/api/ingredients/nguyen-lieu-test')
        ->assertOk()
        ->assertJsonPath('ingredient.id', $ing->id)
        ->assertJsonPath('fruit_slug', null)
        ->assertJsonPath('dishes', []);
    $this->getJson('/api/ingredients/khong-ton-tai-xyz')->assertNotFound();
});

it('API danh sách: sắp xếp kcal và lọc chỉ nguyên liệu có số', function () {
    ingFixture('ing-kcal-thap', 50);
    ingFixture('ing-kcal-cao', 500);
    ingFixture('ing-chua-co-so', null);

    $desc = $this->getJson('/api/ingredients?sort=kcal_desc&per_page=60')->json('data');
    $names = collect($desc)->pluck('slug')->all();
    expect(array_search('ing-kcal-cao', $names))->toBeLessThan(array_search('ing-kcal-thap', $names));

    $withNutrition = $this->getJson('/api/ingredients?with_nutrition=1&per_page=60')->json('data');
    expect(collect($withNutrition)->pluck('slug')->all())->not->toContain('ing-chua-co-so');
});
