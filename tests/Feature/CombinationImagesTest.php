<?php

declare(strict_types=1);

use App\Combinations\CombinationDisplay;
use Database\Seeders\CombinationSeeder;
use Database\Seeders\DishSeeder;
use Database\Seeders\FruitSeeder;
use Database\Seeders\IngredientSeeder;
use Database\Seeders\RegionSeeder;

/* Ảnh trang "Kết hợp cần lưu ý" — dùng lại ảnh nguyên liệu/quả đã có
   (IMG-001), không vẽ ảnh mới. Khóa 3 quy tắc an toàn:
   - phía thuốc luôn không có ảnh (không vẽ vỉ thuốc giả);
   - phía bổ sung dạng viên (nghe/tỏi) luôn không có ảnh nấu ăn;
   - phía nhóm có ảnh thì phải ghi rõ thành viên đại diện. */

beforeEach(function () {
    $this->seed(RegionSeeder::class);
    $this->seed(IngredientSeeder::class);
    $this->seed(DishSeeder::class);
    $this->seed(FruitSeeder::class);
    $this->seed(CombinationSeeder::class);
});

it('trang kết hợp trả đủ 2 phía ảnh cho 38 cặp, theo đúng quy tắc an toàn', function () {
    $all = CombinationDisplay::all();
    expect($all)->toHaveCount(38);

    $both = $one = $none = $images = 0;
    foreach ($all as $c) {
        $a = ! empty($c['side_a_image']);
        $b = ! empty($c['side_b_image']);
        $images += (int) $a + (int) $b;
        if ($a && $b) {
            $both++;
        } elseif ($a || $b) {
            $one++;
        } else {
            $none++;
        }
    }
    // 53/76 phía có ảnh thật; 23 phía còn lại view vẽ placeholder.
    // Con số này khóa cả quy tắc cấm ảnh nấu ăn cho 2 cặp bổ sung dạng viên
    // (nếu ai "tiện tay" lấy ảnh nghệ/tỏi nấu ăn thì tổng sẽ thành 55).
    expect($both)->toBe(18)
        ->and($one)->toBe(17)
        ->and($none)->toBe(3)
        ->and($images)->toBe(53);

    $bySlug = collect($all)->keyBy('slug');

    $crab = $bySlug['cua--ca-chua'];
    expect($crab['side_a_image'])->not->toBeNull()
        ->and($crab['side_a_image']['representative'])->toContain('Cua biển')
        ->and($crab['side_b_image'])->not->toBeNull()
        ->and($crab['side_b_image']['representative'])->toBeNull();

    $simva = $bySlug['buoi--simvastatin'];
    expect($simva['side_a_image'])->not->toBeNull()
        ->and($simva['side_b_image'])->toBeNull();

    foreach (['nghe-bo-sung--warfarin', 'toi-bo-sung--warfarin-aspirin', 'cam-thao--thuoc-ha-ap'] as $slug) {
        expect($bySlug[$slug]['side_a_image'])->toBeNull()
            ->and($bySlug[$slug]['side_b_image'])->toBeNull();
    }

    // Mọi phía thuốc trong toàn bộ 38 cặp đều không có ảnh.
    foreach ($all as $c) {
        if ($c['category'] === 'drug_food') {
            // Phía thuốc là phía còn lại sau phía thực phẩm; kiểm tra cả hai
            // phía không thể phân biệt chỉ bằng category nên khóa ở mức:
            // cặp thuốc × thực phẩm có tối đa 1 ảnh (phía thực phẩm).
            $count = (int) ! empty($c['side_a_image']) + (int) ! empty($c['side_b_image']);
            expect($count)->toBeLessThanOrEqual(1);
        }
    }
});
