<?php

declare(strict_types=1);

use App\Combinations\CombinationDisplay;
use Database\Seeders\CombinationSeeder;
use Database\Seeders\DishSeeder;
use Database\Seeders\FruitSeeder;
use Database\Seeders\IngredientSeeder;
use Database\Seeders\RegionSeeder;

/* Trang "Kết hợp cần lưu ý" gộp theo phía đầu — khóa 3 quy tắc:
   - 38 cặp → 24 nhóm, không mất cặp nào, một cặp chỉ thuộc một nhóm;
   - bưởi là 1 nhóm 3 cặp, mỗi cặp giữ lưu ý riêng (không gộp thành một
     cảnh báo chung, vì simvastatin tránh hẳn còn atorvastatin chỉ tránh
     lượng lớn);
   - nhóm "Cua" (food_group) khác cặp "Cua biển" (ingredient) — gộp theo
     ref, không gộp theo chuỗi tên. */

beforeEach(function () {
    $this->seed(RegionSeeder::class);
    $this->seed(IngredientSeeder::class);
    $this->seed(DishSeeder::class);
    $this->seed(FruitSeeder::class);
    $this->seed(CombinationSeeder::class);
});

it('gộp 38 cặp thành 24 nhóm, mỗi cặp chỉ thuộc một nhóm', function () {
    $groups = CombinationDisplay::groups();
    expect($groups)->toHaveCount(24)
        ->and(collect($groups)->sum('count'))->toBe(38)
        ->and(collect($groups)->flatMap(fn ($g) => collect($g['pairs'])->map->slug)->unique())->toHaveCount(38);
});

it('nhóm bưởi có đúng 3 cặp với lưu ý riêng từng thuốc', function () {
    $buoi = CombinationDisplay::group('buoi');
    expect($buoi)->not->toBeNull()
        ->and($buoi['count'])->toBe(3)
        ->and(collect($buoi['pairs'])->map->slug->sort()->values()->all())
        ->toBe(['buoi--atorvastatin', 'buoi--chen-kenh-canxi', 'buoi--simvastatin'])
        ->and(collect($buoi['pairs'])->map->condition_vi->unique())->toHaveCount(3)
        ->and($buoi['url'])->toBe('/hoa-qua/buoi');
});

it('nhóm cua và cặp cua biển là hai nhóm khác nhau', function () {
    expect(CombinationDisplay::group('cua')['count'])->toBe(4)
        ->and(CombinationDisplay::group('cua-bien')['count'])->toBe(1)
        ->and(CombinationDisplay::group('khong-co'))->toBeNull();
});

it('trang danh sách và trang chi tiết nhóm mở được, chi tiết đủ nội dung', function () {
    $this->get('/ket-hop-can-luu-y')->assertOk()
        ->assertSee('24 nhóm · 38 cặp', false)
        ->assertSee('Bưởi', false);
    $this->get('/ket-hop-can-luu-y/buoi')->assertOk()
        ->assertSee('Atorvastatin (thuốc hạ mỡ máu nhóm statin)', false)
        ->assertSee('Simvastatin (thuốc hạ mỡ máu nhóm statin)', false)
        ->assertSee('Furanocoumarin', false)
        ->assertSee('Nhãn DailyMed của atorvastatin', false);
    $this->get('/ket-hop-can-luu-y/khong-co')->assertNotFound();
});
