<?php

declare(strict_types=1);
use App\Models\Ingredient;
use App\Models\IngredientMedia;

/*
 * Thumbnail cho thẻ danh sách (quy tắc 2026-11-03): route
 * /media/thumb/{400,800}/{dishes,ingredients}/<slug>.webp sinh bản thu
 * nhỏ từ ảnh gốc trong data/media/, giữ tỉ lệ, không upscale, cache
 * trình duyệt giống ảnh gốc (7 ngày + ETag + 304). Thumbnail là cache
 * suy ra, không phải dữ liệu mới nên không vào MANIFEST.
 *
 * Dùng atiso.webp thật trong repo làm fixture (1280×853) thay vì ảnh
 * 1×1 tổng hợp — ảnh 1×1 không chứng minh được việc thu nhỏ.
 */

test('route thumbnail trả đúng kích thước, nhẹ hơn ảnh gốc và cache 304', function () {
    $original = base_path('data/media/ingredients/atiso.webp');
    expect(is_file($original))->toBeTrue();

    $resp = $this->get('/media/thumb/400/ingredients/atiso.webp')->assertOk()
        ->assertHeader('Content-Type', 'image/webp');
    // BinaryFileResponse không trả thân qua getContent() trong test nên
    // đo trực tiếp file cache đã sinh (cùng file mà route phục vụ).
    $cached400 = storage_path('app/thumbnails/400/ingredients/atiso.webp');
    expect(is_file($cached400))->toBeTrue();
    $size = getimagesize($cached400);
    expect($size[0])->toBe(400)->and($size[1])->toBe(267);
    expect(filesize($cached400))->toBeLessThan(filesize($original));

    $resp800 = $this->get('/media/thumb/800/ingredients/atiso.webp')->assertOk();
    $cached800 = storage_path('app/thumbnails/800/ingredients/atiso.webp');
    $size800 = getimagesize($cached800);
    expect($size800[0])->toBe(800)->and($size800[1])->toBe(533);

    $etag = $resp800->headers->get('ETag');
    expect($etag)->not->toBeNull();
    $this->get('/media/thumb/800/ingredients/atiso.webp', ['If-None-Match' => $etag])
        ->assertStatus(304);
});

test('route thumbnail từ chối chiều rộng ngoài danh sách và đường dẫn lạ', function () {
    $this->get('/media/thumb/999/ingredients/atiso.webp')->assertNotFound();
    $this->get('/media/thumb/400/ingredients/../MANIFEST.json')->assertNotFound();
    $this->get('/media/thumb/400/dishes/khong-ton-tai.webp')->assertNotFound();
});

test('API ảnh chính kèm srcset thumbnail, url gốc không đổi', function () {
    $ing = Ingredient::create([
        'name_vi' => 'Nguyên liệu mẫu thumbnail', 'slug' => 'nguyen-lieu-mau-thumb',
        'category' => 'rau-cu', 'mapping_confidence' => 'high',
        'source' => 'curated', 'confidence' => 'high',
    ]);
    IngredientMedia::create([
        'ingredient_id' => $ing->id, 'path' => 'media/ingredients/atiso.webp',
        'license' => 'ai-minh-hoa', 'attribution' => 'Ảnh minh hoạ do AI tạo — Lắc Đói',
        'is_primary' => true,
    ]);
    $list = $this->getJson('/api/ingredients?q='.urlencode('Nguyên liệu mẫu thumbnail'))->assertOk();
    expect($list->json('data.0.image.url'))->toBe('/media/ingredients/atiso.webp')
        ->and($list->json('data.0.image.srcset'))
        ->toBe('/media/thumb/400/ingredients/atiso.webp 400w, /media/thumb/800/ingredients/atiso.webp 800w');
});
