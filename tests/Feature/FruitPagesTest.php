<?php

declare(strict_types=1);

use App\Models\Fruit;
use App\Models\Ingredient;
use App\Models\User;

/*
 * Nhánh hoa quả (FRUIT-005/006): API công khai lọc theo tháng + tìm theo tên
 * gọi khác; module quản trị /admin/hoa-qua giữ kỷ luật V17 (giá bắt buộc
 * nguồn, cửa sổ nằm trong tháng có hàng).
 */

function fruitIngredient(string $slug, string $name, array $snapshot = []): Ingredient
{
    return Ingredient::create([
        'name_vi' => $name,
        'name_en' => $name,
        'slug' => $slug,
        'category' => 'trai-cay',
        'mapping_confidence' => 'high',
        'source' => 'curated',
        'confidence' => 'high',
        'needs_review' => true,
        'nutrition_snapshot' => $snapshot,
    ]);
}

function fruitProfile(Ingredient $ingredient, array $overrides = []): Fruit
{
    $fruit = Fruit::create(array_merge([
        'slug' => $ingredient->slug,
        'ingredient_id' => $ingredient->id,
        'classification_group' => 'nhiet-doi',
        'family' => 'Testaceae',
        'origin' => 'trong-nuoc',
        'grown_in' => [['region' => 'Tiền Giang', 'note' => 'vườn mẫu']],
        'availability_months' => [3, 4, 5, 6],
        'main_season_months' => [4, 5],
        'availability_type' => 'theo-mua',
        'best_nutrition_months' => [4, 5],
        'best_nutrition_note' => 'Đang vụ.',
        'best_value_months' => [5, 6],
        'best_value_note' => 'Giữa vụ giá mềm.',
        'pairing_cautions' => [],
        'needs_review' => true,
    ], $overrides));
    $fruit->prices()->create([
        'region_code' => 'nam', 'month' => 5,
        'price_min' => 20000, 'price_max' => 30000,
        'source_name' => 'Bản tin giá mẫu', 'source_url' => null,
        'collected_at' => '2026-10-31',
    ]);

    return $fruit;
}

test('API công khai lọc quả theo tháng có hàng và tháng giá hời', function () {
    fruitProfile(fruitIngredient('qua-mua', 'Quả mùa'));
    fruitProfile(fruitIngredient('qua-quanh-nam', 'Quả quanh năm'), [
        'availability_months' => range(1, 12), 'main_season_months' => [10],
        'best_nutrition_months' => [10], 'best_value_months' => [10],
    ]);

    $this->getJson('/api/fruits?month=5')
        ->assertOk()
        ->assertJsonPath('total', 2);
    $this->getJson('/api/fruits?month=10')
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.slug', 'qua-quanh-nam');
    $this->getJson('/api/fruits?value_month=6')
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.slug', 'qua-mua');
});

test('tìm quả theo tên gọi khác (tên vùng miền)', function () {
    $ingredient = fruitIngredient('dua-thom-test', 'Dứa thơm');
    $ingredient->aliases()->create(['name' => 'Khóm', 'note' => 'miền Nam']);
    fruitProfile($ingredient);

    $this->getJson('/api/fruits?q='.urlencode('Khóm'))
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.slug', 'dua-thom-test')
        ->assertJsonPath('data.0.aliases.0.name', 'Khóm');
});

test('chi tiết quả trả cửa sổ mùa vụ, giá có nguồn và cờ chưa kiểm định', function () {
    $ingredient = fruitIngredient('qua-nguon-phu', 'Quả nguồn phụ', [
        'kcal_per_100g' => 50.0, 'fct_source' => 'mext_jp',
    ]);
    fruitProfile($ingredient);

    $this->getJson('/api/fruits/qua-nguon-phu')
        ->assertOk()
        ->assertJsonPath('fruit.best_value.months', [5, 6])
        ->assertJsonPath('ingredient.nutrition.unverified', true)
        ->assertJsonPath('prices.0.source_name', 'Bản tin giá mẫu')
        ->assertJsonPath('prices.0.region_code', 'nam');
});

test('admin: khách chưa đăng nhập không vào được module hoa quả', function () {
    $this->getJson('/admin/api/fruits')->assertUnauthorized();
});

test('admin: tạo hồ sơ quả + duyệt; chặn giá thiếu nguồn và cửa sổ ngoài tháng có hàng', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $ingredient = fruitIngredient('qua-moi', 'Quả mới');
    $base = [
        'ingredient_id' => $ingredient->id,
        'classification_group' => 'mong',
        'origin' => 'nhap-khau',
        'availability_months' => [6, 7, 8],
        'availability_type' => 'theo-mua',
    ];

    // Giá thiếu nguồn → 422 (kỷ luật FRUIT-MODULE §3).
    $this->postJson('/admin/api/fruits', [...$base, 'prices' => [[
        'region_code' => 'bac', 'month' => 7, 'price_min' => 10000, 'price_max' => 20000,
        'collected_at' => '2026-10-31',
    ]]])->assertUnprocessable();

    // Cửa sổ giá hời ngoài tháng có hàng → 422 (V17).
    $this->postJson('/admin/api/fruits', [...$base, 'best_value_months' => [12]])
        ->assertUnprocessable();

    // Giá ở tháng không có hàng → 422.
    $this->postJson('/admin/api/fruits', [...$base, 'prices' => [[
        'region_code' => 'bac', 'month' => 1, 'price_min' => 10000, 'price_max' => 20000,
        'source_name' => 'Nguồn mẫu', 'collected_at' => '2026-10-31',
    ]]])->assertUnprocessable();

    $created = $this->postJson('/admin/api/fruits', [...$base, 'prices' => [[
        'region_code' => 'bac', 'month' => 7, 'price_min' => 10000, 'price_max' => 20000,
        'source_name' => 'Nguồn mẫu', 'collected_at' => '2026-10-31',
    ]]])->assertCreated()->json();
    expect($created['needs_review'])->toBeTrue()
        ->and($created['prices'])->toHaveCount(1);

    $this->postJson("/admin/api/fruits/{$created['id']}/review", ['approve' => true])
        ->assertOk()
        ->assertJsonPath('needs_review', false);
});
