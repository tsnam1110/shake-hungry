<?php

declare(strict_types=1);

use App\Models\Drink;
use App\Nutrition\DrinkNutritionService;
use Database\Seeders\DrinkSeeder;
use Database\Seeders\IngredientSeeder;

/* Hoàn thiện lõi nước uống 2026-11-03 (QT-9 + nhánh ethanol):
 * - Engine cộng kcal tham chiếu nên kcal rượu đã bao gồm ethanol — không
 *   cần logic ethanol riêng; Gin & Tonic (gin 231/100g + tonic 34/100g)
 *   phải tính được và chứa phần kcal của gin.
 * - Old Fashioned có whiskey chưa gắn slug (45ml) thì KHÔNG được trả số
 *   đường viên ~9 kcal như số đủ — phải unknown vì missing ≥30% khối lượng.
 * - Đồ đóng gói không tính từ công thức (chỉ đọc nhãn).
 */

beforeEach(function () {
    $this->seed(IngredientSeeder::class);
    $this->seed(DrinkSeeder::class);
});

it('gin-tonic tính được và chứa kcal của gin (ethanol đã nằm trong kcal tham chiếu)', function () {
    $drink = Drink::where('slug', 'gin-tonic')->firstOrFail();
    $n = app(DrinkNutritionService::class)->compute($drink);

    expect($n['status'])->toBe('computed')
        ->and($n['kcal_min'])->toBeGreaterThan(100)
        ->and($n['kcal_max'])->toBeLessThan(200)
        ->and($n['missing'])->toBe([]);
});

it('old-fashioned unknown vì whiskey chưa gắn slug chiếm phần lớn khối lượng', function () {
    $drink = Drink::where('slug', 'old-fashioned')->firstOrFail();
    $n = app(DrinkNutritionService::class)->compute($drink);

    expect($n['status'])->toBe('unknown')
        ->and($n['kcal_min'])->toBeNull();
});

it('đồ đóng gói không tính từ công thức', function () {
    $drink = Drink::where('kind', 'dong-goi')->firstOrFail();
    $n = app(DrinkNutritionService::class)->compute($drink);

    expect($n['status'])->toBe('unknown');
});

it('cà phê sữa đá tính được từ sữa đặc + cà phê đen', function () {
    $drink = Drink::where('slug', 'ca-phe-sua-da')->firstOrFail();
    $n = app(DrinkNutritionService::class)->compute($drink);

    expect($n['status'])->toBe('computed')
        ->and($n['kcal_min'])->toBeGreaterThan(50);
});
