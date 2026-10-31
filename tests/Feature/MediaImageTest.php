<?php

declare(strict_types=1);

use App\Models\Dish;
use App\Models\DishMedia;
use App\Models\Ingredient;
use App\Models\IngredientMedia;

/*
 * IMG-001: ảnh cho món/nguyên liệu/hoa quả — API công khai trả ảnh chính
 * (URL + license + ghi công + cờ AI minh hoạ), route /media phục vụ file
 * trong data/media/ và chỉ đúng khuôn cho phép.
 */

function mediaDish(string $slug, string $license = 'cc-by-4.0'): Dish
{
    $dish = Dish::create([
        'slug' => $slug, 'country_code' => 'VN', 'status' => 'draft',
        'source' => 'curated', 'confidence' => 'low', 'needs_review' => true,
        'completeness_level' => 1, 'is_eat_out' => true, 'is_home_cook' => true,
    ]);
    $dish->names()->create(['locale' => 'vi', 'name' => $slug, 'is_primary' => true, 'is_alias' => false]);
    DishMedia::create([
        'dish_id' => $dish->id, 'path' => "media/dishes/{$slug}.webp",
        'license' => $license, 'attribution' => 'Tác Giả Mẫu / Wikimedia Commons (CC BY 4.0)',
        'is_primary' => true,
    ]);

    return $dish->fresh();
}

test('API chi tiết món trả ảnh chính kèm license, ghi công và cờ minh hoạ', function () {
    mediaDish('mon-co-anh-that', 'cc-by-sa-4.0');
    $resp = $this->getJson('/api/dishes/mon-co-anh-that')->assertOk();
    $resp->assertJsonPath('dish.image.url', '/media/dishes/mon-co-anh-that.webp')
        ->assertJsonPath('dish.image.license', 'cc-by-sa-4.0')
        ->assertJsonPath('dish.image.is_ai', false);

    mediaDish('mon-anh-minh-hoa', 'ai-minh-hoa');
    $this->getJson('/api/dishes/mon-anh-minh-hoa')->assertOk()
        ->assertJsonPath('dish.image.is_ai', true);

    $plain = Dish::create([
        'slug' => 'mon-chua-co-anh', 'country_code' => 'VN', 'status' => 'draft',
        'source' => 'curated', 'confidence' => 'low', 'needs_review' => true,
        'completeness_level' => 1, 'is_eat_out' => true, 'is_home_cook' => true,
    ]);
    $plain->names()->create(['locale' => 'vi', 'name' => 'mon-chua-co-anh', 'is_primary' => true, 'is_alias' => false]);
    $this->getJson('/api/dishes/mon-chua-co-anh')->assertOk()
        ->assertJsonPath('dish.image', null);
});

test('API danh sách món và nguyên liệu đều mang trường image', function () {
    mediaDish('mon-trong-danh-sach');
    $list = $this->getJson('/api/dishes?q=mon-trong-danh-sach')->assertOk();
    expect($list->json('data.0.image.url'))->toBe('/media/dishes/mon-trong-danh-sach.webp');

    $ing = Ingredient::create([
        'name_vi' => 'Nguyên liệu mẫu có ảnh', 'slug' => 'nguyen-lieu-mau-anh', 'category' => 'rau-cu',
        'mapping_confidence' => 'high', 'source' => 'curated', 'confidence' => 'high',
    ]);
    IngredientMedia::create([
        'ingredient_id' => $ing->id, 'path' => 'media/ingredients/nguyen-lieu-mau-anh.webp',
        'license' => 'cc0', 'attribution' => null, 'is_primary' => true,
    ]);
    $ings = $this->getJson('/api/ingredients?q='.urlencode('Nguyên liệu mẫu có ảnh'))->assertOk();
    expect($ings->json('data.0.image.url'))->toBe('/media/ingredients/nguyen-lieu-mau-anh.webp')
        ->and($ings->json('data.0.image.is_ai'))->toBeFalse();
});

test('route /media chỉ phục vụ đúng file webp trong data/media', function () {
    $dir = base_path('data/media/dishes');
    $file = $dir.'/zz-test-route.webp';
    $manifestPath = base_path('data/media/MANIFEST.json');
    $manifestBackup = file_get_contents($manifestPath);
    // WebP 1x1 tối thiểu (bytes cố định) + mục MANIFEST tạm để V2 không bắt lỗi file lạ.
    $webp = base64_decode('UklGRiIAAABXRUJQVlA4IBYAAAAwAQCdASoBAAEADsD+JaQAA3AAAAAA');
    file_put_contents($file, $webp);
    try {
        $this->get('/media/dishes/zz-test-route.webp')->assertOk()
            ->assertHeader('Content-Type', 'image/webp');
        $this->get('/media/dishes/khong-ton-tai.webp')->assertNotFound();
        $this->get('/media/../MANIFEST.json')->assertNotFound();
        $this->get('/media/dishes/zz-test-route.png')->assertNotFound();
    } finally {
        @unlink($file);
        file_put_contents($manifestPath, $manifestBackup);
    }
});
