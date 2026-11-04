<?php

declare(strict_types=1);

use App\Models\UserProfile;
use App\Profile\WeekTargetSuggester;

// Gợi ý protein là hàm thuần tuý trên hồ sơ + kcal mục tiêu — test không
// cần database, giống NutritionCalculatorTest. Các con số trong test là
// phép tính tay từ công thức, KHÔNG phải số Mealbird công bố (ví dụ của họ
// 175 g → giới hạn 145 g không tái hiện chính xác từ các bước đã công bố,
// nên không dùng làm kỳ vọng — xem tài liệu Giai đoạn 1).

function suggester(): WeekTargetSuggester
{
    return new WeekTargetSuggester;
}

function profile(?float $heightCm): UserProfile
{
    return new UserProfile(['height_cm' => $heightCm]);
}

it('giới hạn 35% kcal thắng khi protein thô cao hơn giới hạn', function () {
    // Chiều cao 180 cm → BMI 25: 25 × 1,8² = 81,0 kg = 178,6 lb → thô 179 g.
    // Kcal 2.000 → giới hạn 2.000 × 0,35 / 4 = 175 g → gợi ý 175 g.
    $result = suggester()->suggest(profile(180.0), 2000);

    expect($result)->not->toBeNull()
        ->and($result['weight_at_bmi25_kg'])->toBe(81.0)
        ->and($result['protein_raw_g'])->toBe(179)
        ->and($result['protein_cap_g'])->toBe(175)
        ->and($result['suggested_g'])->toBe(175)
        ->and($result['kcal_used'])->toBe(2000);
});

it('protein thô thắng khi giới hạn kcal cao hơn protein thô', function () {
    // Cùng chiều cao 180 cm → thô 179 g.
    // Kcal 3.000 → giới hạn 3.000 × 0,35 / 4 = 262,5 → làm tròn 263 g → gợi ý 179 g.
    $result = suggester()->suggest(profile(180.0), 3000);

    expect($result)->not->toBeNull()
        ->and($result['protein_raw_g'])->toBe(179)
        ->and($result['protein_cap_g'])->toBe(263)
        ->and($result['suggested_g'])->toBe(179);
});

it('trả null khi thiếu chiều cao, thiếu kcal, hoặc kcal không dương', function () {
    expect(suggester()->suggest(profile(null), 2000))->toBeNull()
        ->and(suggester()->suggest(profile(180.0), null))->toBeNull()
        ->and(suggester()->suggest(profile(180.0), 0))->toBeNull();
});
