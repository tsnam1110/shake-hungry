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

it('kho có 38 bản ghi (đợt 1 + đợt 2) và tầng suy ra đúng từ evidence_class', function () {
    expect(Combination::count())->toBe(38)
        ->and(Combination::where('evidence_class', 'E1')->count())->toBe(10)
        ->and(Combination::where('evidence_class', 'E2')->count())->toBe(9)
        ->and(Combination::where('evidence_class', 'E3')->count())->toBe(19);

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

it('đợt 2: MAOI × tyramin ở tầng 1 trên trang bia; cặp bổ sung tỏi chỉ áp dạng viên', function () {
    $bia = $this->getJson('/api/ingredients/bia')->assertOk()->json('ingredient.combinations');
    $maoi = collect($bia)->firstWhere('slug', 'tyramin--maoi');
    expect($maoi)->not->toBeNull()
        ->and($maoi['tier'])->toBe(1)
        ->and($maoi['other']['type'])->toBe('drug_group')
        ->and($maoi['condition_vi'])->toContain('CHỐNG CHỈ ĐỊNH');

    $toi = $this->getJson('/api/ingredients/toi')->assertOk()->json('ingredient.combinations');
    $supp = collect($toi)->firstWhere('slug', 'toi-bo-sung--warfarin-aspirin');
    expect($supp)->not->toBeNull()
        ->and($supp['tier'])->toBe(2)
        ->and($supp['condition_vi'])->toContain('VIÊN BỔ SUNG')
        ->and($supp['condition_vi'])->toContain('KHÔNG nằm trong cảnh báo');
});

it('đợt 2: cam thảo tồn tại ở mức dữ liệu nhưng không gắn vào trang nguyên liệu nào', function () {
    $licorice = Combination::where('slug', 'cam-thao--thuoc-ha-ap')->firstOrFail();
    expect($licorice->tier())->toBe(2)
        ->and($licorice->side('a')['members'])->toBe([]);
    foreach (['sua-tuoi', 'toi', 'bia', 'gung'] as $slug) {
        $rows = $this->getJson("/api/ingredients/{$slug}")->assertOk()->json('ingredient.combinations');
        expect(collect($rows)->pluck('slug'))->not->toContain('cam-thao--thuoc-ha-ap');
    }
});

it('đợt 2: món có dưa leo + cà chua nhận cặp dân gian vào folk, không vào notes', function () {
    $c = $this->getJson('/api/dishes/com-ga-tam-ky')->assertOk()->json('combinations');
    expect(collect($c['folk'])->pluck('slug'))->toContain('dua-leo--ca-chua')
        ->and(collect($c['notes'])->pluck('slug'))->not->toContain('dua-leo--ca-chua');
});

it('cặp kết hợp KHÔNG lọc món khỏi catalog và không gắn tuyến thuốc vào món', function () {
    // Catalog giới hạn per_page tối đa 48 (CatalogController) — duyệt hết
    // các trang theo meta.last_page của chính response, không đoán cỡ trang.
    $all = collect();
    $page = 1;
    do {
        $resp = $this->getJson("/api/dishes?per_page=48&page={$page}")->assertOk();
        $all = $all->merge(collect($resp->json('data') ?? [])->pluck('slug'));
        $last = (int) ($resp->json('last_page') ?? $resp->json('meta.last_page') ?? $page);
        $page++;
    } while ($page <= $last);
    // Bún riêu cua (cua + cà chua) vẫn hiện diện bình thường trong catalog,
    // và catalog trả đủ mọi món (lớp kết hợp không lọc bỏ món nào).
    expect($all)->toContain('bun-rieu-cua')
        ->and($all->unique()->count())->toBe(\App\Models\Dish::count());

    // Không món nào nhận cặp có phía thuốc (tuyến thuốc chỉ ở trang nguyên liệu/quả).
    foreach (['bun-rieu-cua', 'hu-tieu'] as $slug) {
        $c = $this->getJson("/api/dishes/{$slug}")->json('combinations');
        foreach ([...$c['notes'], ...$c['folk']] as $row) {
            expect($row['category'])->toBe('food_food');
        }
    }
});
