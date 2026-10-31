<?php

declare(strict_types=1);

use App\Models\Dish;
use App\Models\Region;
use App\Models\Tag;
use App\Models\User;

/*
 * DATA-010: vùng miền của món chỉ còn 3 miền chính (+ cả nước); tỉnh/thành
 * gắn liền với món chuyển thành phân loại "Đặc sản" (facet dac-san) —
 * vẫn lọc/tìm được như một nhãn thường.
 */

function specialtyRegion(string $code, string $name, string $level, ?int $parentId = null): Region
{
    return Region::create([
        'code' => $code, 'name' => $name, 'level' => $level,
        'parent_id' => $parentId, 'country_code' => 'VN',
    ]);
}

test('API vùng miền công khai chỉ còn cả nước và 3 miền chính', function () {
    $country = specialtyRegion('vn', 'Việt Nam', 'country');
    $macro = specialtyRegion('vn-central', 'Trung Bộ', 'macro_region', $country->id);
    specialtyRegion('vn-hue', 'Huế', 'province', $macro->id);

    $resp = $this->getJson('/api/regions')->assertOk();
    $names = array_column($resp->json(), 'name');
    expect($names)->toContain('Việt Nam', 'Trung Bộ')
        ->and($names)->not->toContain('Huế');
});

test('món gắn nhãn đặc sản lọc được qua API danh mục theo tỉnh', function () {
    $dish = Dish::create([
        'slug' => 'mon-dac-san-hue', 'country_code' => 'VN', 'status' => 'draft',
        'source' => 'curated', 'confidence' => 'low', 'needs_review' => true,
        'completeness_level' => 1, 'is_eat_out' => true, 'is_home_cook' => true,
    ]);
    $dish->names()->create(['locale' => 'vi', 'name' => 'Món đặc sản Huế', 'is_primary' => true, 'is_alias' => false]);
    $dish->tags()->attach(Tag::firstOrCreate(['slug' => 'dac-san:hue'], ['name' => 'Đặc sản Huế'])->id);
    $dish->tags()->attach(Tag::firstOrCreate(['slug' => 'loai:bun-pho-mi'], ['name' => 'Bún · Phở · Mì'])->id);

    $hit = $this->getJson('/api/dishes?tag=dac-san:hue')->assertOk();
    expect(array_column($hit->json('data'), 'slug'))->toContain('mon-dac-san-hue');

    $miss = $this->getJson('/api/dishes?tag=dac-san:ha-noi')->assertOk();
    expect(array_column($miss->json('data'), 'slug'))->not->toContain('mon-dac-san-hue');
});

test('admin không gán được vùng cấp tỉnh cho món — chỉ cả nước và 3 miền', function () {
    $country = specialtyRegion('vn', 'Việt Nam', 'country');
    $macro = specialtyRegion('vn-north', 'Bắc Bộ', 'macro_region', $country->id);
    $province = specialtyRegion('vn-ha-noi', 'Hà Nội', 'province', $macro->id);
    $admin = User::factory()->create(['role' => 'admin']);

    $bad = $this->actingAs($admin)->postJson('/admin/api/dishes', [
        'primary_name' => 'Món kiểm tra vùng',
        'region_id' => $province->id,
    ]);
    $bad->assertJsonValidationErrors('region_id');

    $good = $this->actingAs($admin)->postJson('/admin/api/dishes', [
        'primary_name' => 'Món kiểm tra vùng',
        'region_id' => $macro->id,
    ]);
    $good->assertCreated();
});
