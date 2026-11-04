<?php

declare(strict_types=1);

use App\Models\Drink;

// Quy định cocktail, bước 3/4 (2026-11-04): thư viện giữ công tắc
// "Không cồn trước" (mặc định bật, JS ép alcohol=0 khi chưa chọn lọc
// Cồn) + huy hiệu 18+ từ cờ alcohol của API, và không đưa cocktail lên
// trang chủ / gợi ý nổi bật (trang chủ và Lắc chỉ trả Dish).
// Test này khoá phần API + giao diện danh sách mà test có thể kiểm:
// lọc alcohol của API và sự hiện diện của công tắc trong trang /do-uong.

beforeEach(function () {
    Drink::create([
        'slug' => 'mojito-test', 'name_vi' => 'Mojito test',
        'drink_group' => 'cocktail-co-con', 'kind' => 'pha-che', 'alcohol' => true,
    ]);
    Drink::create([
        'slug' => 'tra-da-test', 'name_vi' => 'Trà đá test',
        'drink_group' => 'tra', 'kind' => 'pha-che', 'alcohol' => false,
    ]);
});

it('lọc cồn của API tách đúng đồ có cồn và không cồn', function () {
    $noAlcohol = $this->getJson('/api/drinks?alcohol=0')->assertOk()->json('data');
    expect(collect($noAlcohol)->pluck('slug')->all())->toBe(['tra-da-test']);

    $alcohol = $this->getJson('/api/drinks?alcohol=1')->assertOk()->json('data');
    expect(collect($alcohol)->pluck('slug')->all())->toBe(['mojito-test'])
        ->and(collect($alcohol)->first()['alcohol'])->toBeTrue();
});

it('trang danh sách đồ uống giữ công tắc Không cồn trước mặc định bật', function () {
    $this->get('/do-uong')->assertOk()
        ->assertSee('Không cồn trước', false)
        ->assertSee('id="no-alcohol-first"', false);
});
