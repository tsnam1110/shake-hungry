<?php

declare(strict_types=1);

use App\Nutrition\IngredientInput;
use App\Nutrition\NutritionCalculator;
use Illuminate\Support\Facades\DB;

/**
 * TEST-002 — chốt lại hai thứ đã kiểm chứng trên bộ FNDDS/Nutrition5k, để lần sau
 * sửa engine mà làm hỏng thì test đỏ ngay, không phải chờ chạy lại phép đo.
 */
it('lenh accuracy-run bao loi ro rang khi chua nhap bo kiem chung', function () {
    DB::table('fndds_food_inputs')->delete();
    DB::table('fndds_foods')->delete();

    $this->artisan('lacdoi:accuracy-run --dataset=fndds')
        ->expectsOutputToContain('Bảng fndds_foods trống')
        ->assertExitCode(1);
});

it('engine cong dung tren mot me lon roi quy ve 100g — nen tang cua phep do FNDDS', function () {
    // Tái hiện đúng phép tính của lacdoi:accuracy-run: SR Links cho gram của CẢ MẺ,
    // phải chuẩn hoá về 100 g mới so được với kcal/100g USDA công bố. Bỏ bước chuẩn
    // hoá này là nguồn của bias +400% đã gặp lần chạy đầu 2026-08-31.
    $result = (new NutritionCalculator)->compute(
        [
            new IngredientInput(slug: 'a', grams: 900.0, mappingConfidence: 'high',
                fctSource: 'usda_sr_legacy', kcalPer100g: 100.0),
            new IngredientInput(slug: 'b', grams: 100.0, mappingConfidence: 'high',
                fctSource: 'usda_sr_legacy', kcalPer100g: 500.0),
        ],
        cookingMethod: 'nau', fctSource: 'usda_sr_legacy', portionWeighed: true,
    );

    // Tổng mẻ: 900 g × 1 kcal/g + 100 g × 5 kcal/g = 1.400 kcal trên 1.000 g
    expect($result->kcalMin)->toBe(1400.0)
        ->and($result->totalRawG)->toBe(1000.0);

    $per100g = $result->kcalMin * 100.0 / $result->totalRawG;
    expect($per100g)->toBe(140.0);
});

it('mon hap co yield factor cho khoi luong thanh pham dung ty le', function () {
    // Bằng chứng từ phép đo FNDDS: cá hấp có hệ số khối lượng ngầm định 0,79 —
    // engine phải suy được khối lượng thành phẩm khi nguyên liệu có yield factor,
    // vì đó là mẫu số của kcal/100g món chín (CALORIE-ENGINE §3).
    $result = (new NutritionCalculator)->compute(
        [new IngredientInput(slug: 'ca', grams: 100.0, mappingConfidence: 'high',
            fctSource: 'usda_sr_legacy', kcalPer100g: 100.0, yieldFactor: 0.79)],
        cookingMethod: 'hap', fctSource: 'usda_sr_legacy', portionWeighed: true,
    );

    // Chất dinh dưỡng KHÔNG đổi khi mất nước; chỉ khối lượng đổi.
    expect($result->kcalMin)->toBe(100.0)
        ->and($result->cookedGramsEstimate)->toBe(79.0);

    // Quy về 100 g THÀNH PHẨM thì đậm đặc hơn — đúng chiều với dữ liệu USDA.
    $per100gCooked = $result->kcalMin * 100.0 / $result->cookedGramsEstimate;
    expect(round($per100gCooked, 1))->toBe(126.6);
});
