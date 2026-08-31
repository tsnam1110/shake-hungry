<?php

declare(strict_types=1);

use App\Models\Dish;
use Illuminate\Support\Facades\DB;

/**
 * DATA-006 DoD: có test cho ít nhất V1 (chốt chặn QT-1) và V5, và phải chứng
 * minh cổng ĐỎ ĐƯỢC — bản ghi sai được chèn bằng cách tạm gỡ ràng buộc R1/R2
 * (mô phỏng "code có lỗi ghi bậy vào DB"), rồi validator phải bắt được.
 */
it('V1: bao do khi co ban ghi dinh duong nguon LLM lot vao database', function () {
    $dish = Dish::create([
        'slug' => 'test-dish-v1', 'country_code' => 'VN',
        'completeness_level' => 1, 'source' => 'curated', 'confidence' => 'low',
    ]);

    // Gỡ chốt R1 để mô phỏng dữ liệu bẩn lọt qua — validator là lớp phòng thủ thứ hai
    DB::statement('ALTER TABLE dish_nutrition DROP CONSTRAINT dish_nutrition_r1_no_llm');
    DB::table('dish_nutrition')->insert([
        'dish_id' => $dish->id, 'basis' => 'per_serving',
        'kcal_min' => 100, 'kcal_max' => 200, 'confidence' => 'low',
        'fct_source' => 'usda_sr_legacy', 'missing_ingredients' => '[]',
        'computed_at' => now(), 'engine_version' => '1.0.0',
        'source' => 'llm_suggested',
    ]);

    $this->artisan('lacdoi:validate-data')->assertExitCode(1);
});

it('V5: bao do khi kcal_min > kcal_max lot vao database', function () {
    $dish = Dish::create([
        'slug' => 'test-dish-v5', 'country_code' => 'VN',
        'completeness_level' => 1, 'source' => 'curated', 'confidence' => 'low',
    ]);

    DB::statement('ALTER TABLE dish_nutrition DROP CONSTRAINT dish_nutrition_r2_range');
    DB::table('dish_nutrition')->insert([
        'dish_id' => $dish->id, 'basis' => 'per_serving',
        'kcal_min' => 500, 'kcal_max' => 400, 'confidence' => 'low',
        'fct_source' => 'usda_sr_legacy', 'missing_ingredients' => '[]',
        'computed_at' => now(), 'engine_version' => '1.0.0',
        'source' => 'computed',
    ]);

    $this->artisan('lacdoi:validate-data')->assertExitCode(1);
});

it('xanh tren du lieu that hien tai cua repo', function () {
    // 150 file nguyên liệu thật + database trống bản ghi dẫn xuất ⇒ không lỗi 🔴
    $this->artisan('lacdoi:validate-data')->assertExitCode(0);
});
