<?php

declare(strict_types=1);

namespace App\Profile;

use App\Models\UserProfile;

/**
 * Gợi ý protein mục tiêu cho tuần ăn (Giai đoạn 2, Phần 2.1, Task 1).
 *
 * Quy tắc đã chốt ngày 2026-11-04 ("làm cả 2"): app tính gợi ý theo công
 * thức Mealbird đã công bố, và người dùng tự nhập/ghi đè. Service này chỉ
 * tính GỢI Ý — nó không ghi đè mục tiêu người dùng đã lưu, và không sinh
 * số dinh dưỡng cho món ăn (QT-1: số món vẫn do engine tính từ bảng tham
 * chiếu, không phải do hàm này).
 *
 * Công thức gợi ý (từng bước, để hiển thị được):
 * 1. Cân nặng tại BMI 25 theo chiều cao: 25 × (chiều cao tính bằng mét)² (kg).
 * 2. Protein thô: cân nặng đó đổi ra lb (× 2,20462) × 1 g/lb.
 * 3. Giới hạn: tối đa 35% tổng kcal mục tiêu, chia cho 4 kcal/g protein.
 * 4. Gợi ý = min(protein thô, giới hạn).
 *
 * Trả null khi thiếu dữ kiện (QT-9): không có chiều cao, hoặc không có
 * kcal mục tiêu để tính giới hạn. Không dùng cân nặng thật thay cho cân
 * nặng tại BMI 25, vì đó sẽ là một công thức khác mà không ai chốt.
 *
 * Tỉ lệ điều chỉnh kcal theo mục tiêu — CHỐT ngày 2026-11-04 theo đề xuất
 * đã được Benjamin duyệt (đề xuất main chat 21:44, lấy theo công thức
 * Mealbird đã phân tích, ghi rõ là quy tắc Lắc Đói chọn, KHÔNG phải
 * chuẩn y tế phổ quát): giảm −20% / duy trì 0% / tăng +10%, làm tròn
 * 50 kcal, áp trên kcal Hồ sơ đã nhập, KHÔNG suy từ TDEE. Hàm
 * adjustKcalForGoal() chỉ tính số tuần — nó không ghi đè kcal Hồ sơ,
 * nên tạo tuần mới vẫn tính lại từ số gốc, không cộng dồn tỉ lệ.
 *
 * @return ?array{
 *   height_cm: float,
 *   weight_at_bmi25_kg: float,
 *   weight_at_bmi25_lb: float,
 *   protein_raw_g: int,
 *   protein_cap_g: int,
 *   suggested_g: int,
 *   kcal_used: int,
 *   steps: string,
 * }
 */
class WeekTargetSuggester
{
    public const PROTEIN_PER_LB = 1.0;

    public const PROTEIN_CAP_FRACTION = 0.35;

    public const KCAL_PER_GRAM_PROTEIN = 4.0;

    public const KG_TO_LB = 2.20462;

    /** Tỉ lệ kcal theo goal (đã chốt 2026-11-04, xem docblock trên). */
    public const GOAL_KCAL_FACTORS = ['giam' => 0.8, 'duy-tri' => 1.0, 'tang' => 1.1];

    public const KCAL_ROUNDING = 50;

    /**
     * Kcal mục tiêu của TUẦN từ kcal Hồ sơ và goal. Trả null khi chưa có
     * kcal gốc; goal null hoặc không nhận thì giữ nguyên số gốc (không
     * tự đoán mục tiêu). Làm tròn tới bội 50 gần nhất.
     */
    public function adjustKcalForGoal(?int $baseKcal, ?string $goal): ?int
    {
        if ($baseKcal === null || $baseKcal <= 0) {
            return null;
        }
        $factor = self::GOAL_KCAL_FACTORS[$goal ?? ''] ?? 1.0;

        return (int) round($baseKcal * $factor / self::KCAL_ROUNDING) * self::KCAL_ROUNDING;
    }

    public function suggest(UserProfile $profile, ?int $targetKcalPerDay): ?array
    {
        $heightCm = $profile->height_cm !== null ? (float) $profile->height_cm : null;
        if ($heightCm === null || $heightCm <= 0 || $targetKcalPerDay === null || $targetKcalPerDay <= 0) {
            return null;
        }

        $heightM = $heightCm / 100;
        $weightKg = 25 * $heightM * $heightM;
        $weightLb = $weightKg * self::KG_TO_LB;
        $raw = (int) round($weightLb * self::PROTEIN_PER_LB);
        $cap = (int) round($targetKcalPerDay * self::PROTEIN_CAP_FRACTION / self::KCAL_PER_GRAM_PROTEIN);
        $suggested = min($raw, $cap);

        return [
            'height_cm' => $heightCm,
            'weight_at_bmi25_kg' => round($weightKg, 1),
            'weight_at_bmi25_lb' => round($weightLb, 1),
            'protein_raw_g' => $raw,
            'protein_cap_g' => $cap,
            'suggested_g' => $suggested,
            'kcal_used' => $targetKcalPerDay,
            'steps' => sprintf(
                'Cân nặng tại BMI 25: 25 × %.2f² = %.1f kg (%.1f lb); protein thô: %.1f × 1 g/lb = %d g; '
                .'giới hạn 35%% của %d kcal: %d × 0,35 / 4 = %d g; gợi ý = min(%d, %d) = %d g.',
                $heightM, $weightKg, $weightLb, $weightLb, $raw,
                $targetKcalPerDay, $targetKcalPerDay, $cap, $raw, $cap, $suggested,
            ),
        ];
    }
}
