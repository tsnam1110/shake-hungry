<?php

declare(strict_types=1);

namespace App\Profile;

use App\Models\UserProfile;

/**
 * Nhu cầu năng lượng theo CALORIE-ENGINE §8:
 * - BMR Mifflin–St Jeor (mặc định); Schofield khi thiếu chiều cao.
 * - TDEE = BMR × khoảng PAL của FAO/WHO/UNU 2004 — luôn là KHOẢNG, không số lẻ.
 */
class TdeeCalculator
{
    /** Nhóm hoạt động → khoảng PAL (FAO 2004). */
    public const PAL_BANDS = [
        'nhe' => [1.40, 1.69],
        'vua' => [1.70, 1.99],
        'nang' => [2.00, 2.40],
    ];

    /** Tỷ lệ calo theo bữa (USR-004). */
    public const SLOT_RATIOS = ['sang' => 0.25, 'trua' => 0.35, 'chieu' => 0.10, 'toi' => 0.30, 'an-vat' => 0.10];

    /**
     * @return ?array{bmr: float, tdee_min: int, tdee_max: int, formula: string, note: string}
     */
    public function for(UserProfile $profile, ?int $currentYear = null): ?array
    {
        $weight = $profile->weight_kg !== null ? (float) $profile->weight_kg : null;
        if ($weight === null || $profile->birth_year === null) {
            return null; // thiếu dữ kiện tối thiểu — không bịa số
        }
        $age = ($currentYear ?? (int) date('Y')) - (int) $profile->birth_year;
        $sex = $profile->sex ?? 'unspecified';
        $height = $profile->height_cm !== null ? (float) $profile->height_cm : null;

        if ($height !== null && $sex !== 'unspecified') {
            $bmr = 10 * $weight + 6.25 * $height - 5 * $age + ($sex === 'male' ? 5 : -161);
            $formula = 'Mifflin–St Jeor';
        } else {
            $bmr = $this->schofield($weight, $age, $sex === 'female' ? 'female' : 'male');
            $formula = 'Schofield (FAO/WHO/UNU)'.($sex === 'unspecified' ? ' — tạm tính theo nam vì chưa rõ giới tính' : '');
        }

        [$palMin, $palMax] = self::PAL_BANDS[$profile->activity_level ?? 'nhe'] ?? self::PAL_BANDS['nhe'];

        return [
            'bmr' => round($bmr),
            'tdee_min' => (int) round($bmr * $palMin / 10) * 10,
            'tdee_max' => (int) round($bmr * $palMax / 10) * 10,
            'formula' => $formula,
            'note' => 'Ước lượng theo công thức trên và khoảng mức vận động của FAO (2004); sai số điển hình ±10%.',
        ];
    }

    private function schofield(float $w, int $age, string $sex): float
    {
        // [hệ số nhân, hằng số] theo dải tuổi — CALORIE-ENGINE §8
        $table = [
            'male' => [[3, 59.512, -30.4], [10, 22.706, 504.3], [18, 17.686, 658.2], [30, 15.057, 692.2], [60, 11.472, 873.1], [PHP_INT_MAX, 11.711, 587.7]],
            'female' => [[3, 58.317, -31.1], [10, 20.315, 485.9], [18, 13.384, 692.6], [30, 14.818, 486.6], [60, 8.126, 845.6], [PHP_INT_MAX, 9.082, 658.5]],
        ];
        foreach ($table[$sex] as [$maxAge, $coef, $const]) {
            if ($age < $maxAge) {
                return $coef * $w + $const;
            }
        }

        return 0.0;
    }
}
