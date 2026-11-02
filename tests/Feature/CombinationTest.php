<?php

declare(strict_types=1);

use App\Models\Combination;
use Database\Seeders\CombinationSeeder;
use Database\Seeders\DishSeeder;
use Database\Seeders\FruitSeeder;
use Database\Seeders\IngredientSeeder;
use Database\Seeders\RegionSeeder;

/* FOOD-COMBINE-001: lớp thông tin "Kết hợp cần lưu ý" — tầng suy ra từ
   evidence_class, hiển thị hai chiều ở trang nguyên liệu/quả, gắn vào món
   chỉ với cặp thực phẩm–thực phẩm; TUYỆT ĐỐI không lọc/chặn gợi ý. */

beforeEach(function () {
    $this->seed(RegionSeeder::class);
    $this->seed(IngredientSeeder::class);
    $this->seed(DishSeeder::class);
    $this->seed(FruitSeeder::class);
    $this->seed(CombinationSeeder::class);
});

it('kho có 23 bản ghi đợt 1 và tầng suy ra đúng từ evidence_class', function () {
    expect(Combination::count())->toBe(23)
        ->and(Combination::where('evidence_class', 'E1')->count())->toBe(9)
        ->and(Combination::where('evidence_class', 'E2')->count())->toBe(6)
        ->and(Combination::where('evidence_class', 'E3')->count())->toBe(8);

    $simva = Combination::where('slug', 'buoi--simvastatin')->firstOrFail();
    expect($simva->tier())->toBe(1)
        ->and($simva->tierLabel())->toBe('Tránh kết hợp');

    $folk = Combination::where('slug', 'cua--ca-chua')->firstOrFail();
    expect($folk->tier())->toBe(3)
        ->and($folk->science_status)->toBe('refuted')
        ->and($folk->scienceLabel())->toContain('không cần kiêng');
});

it('trang quả bưởi hiện các cặp thuốc ở tầng 1 với phía còn lại là nhóm thuốc', function () {
    $combos = $this->getJson('/api/fruits/buoi')->assertOk()->json('combinations');
    $slugs = collect($combos)->pluck('slug');
    expect($slugs)->toContain('buoi--simvastatin', 'buoi--atorvastatin', 'buoi--chen-kenh-canxi');
    $simva = collect($combos)->firstWhere('slug', 'buoi--simvastatin');
    expect($simva['tier'])->toBe(1)
        ->and($simva['other']['type'])->toBe('drug_group');
});

it('món chứa cả hai phía: cặp tầng 2 vào notes, cặp dân gian đã bác vào folk thu gọn', function () {
    $folkDish = $this->getJson('/api/dishes/bun-rieu-cua')->assertOk()->json('combinations');
    expect(collect($folkDish['folk'])->pluck('slug'))->toContain('cua--ca-chua')
        ->and($folkDish['notes'])->toBe([]);

    $noteDish = $this->getJson('/api/dishes/hu-tieu')->assertOk()->json('combinations');
    expect(collect($noteDish['notes'])->pluck('slug'))->toContain('gan-lon--gia-do');
});

it('cặp kết hợp KHÔNG lọc món khỏi catalog và không gắn tuyến thuốc vào món', function () {
    $slugs = collect($this->getJson('/api/dishes?per_page=60')->assertOk()->json('data') ?? [])
        ->pluck('slug');
    // Bún riêu cua (cua + cà chua) vẫn hiện diện bình thường trong catalog.
    $all = collect();
    foreach ([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11] as $page) {
        $resp = $this->getJson("/api/dishes?per_page=60&page={$page}")->assertOk();
        $all = $all->merge(collect($resp->json('data') ?? [])->pluck('slug'));
        if (count($resp->json('data') ?? []) < 60) {
            break;
        }
    }
    expect($all)->toContain('bun-rieu-cua');

    // Không món nào nhận cặp có phía thuốc (tuyến thuốc chỉ ở trang nguyên liệu/quả).
    foreach (['bun-rieu-cua', 'hu-tieu'] as $slug) {
        $c = $this->getJson("/api/dishes/{$slug}")->json('combinations');
        foreach ([...$c['notes'], ...$c['folk']] as $row) {
            expect($row['category'])->toBe('food_food');
        }
    }
});
