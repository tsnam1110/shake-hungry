<?php

declare(strict_types=1);

namespace App\Week;

use App\Models\WeekPlan;
use App\Models\WeekPlanDay;

/**
 * Kiểm chứng tuần ăn từng ngày (Giai đoạn 2, Phần 2.3).
 *
 * Tính lại từ món trong ngày, KHÔNG tin số đã lưu ở week_plan_days khi
 * hiển thị — số lưu chỉ là ảnh chụp để liệt kê nhanh. Quy tắc trạng thái:
 * - `chua-du-du-lieu`: có bữa ăn ngoài (không biết đã ăn gì), món thiếu
 *   dinh dưỡng, món có nguyên liệu missing/unverified, món confidence
 *   unknown, thiếu một bữa trong 4 bữa chuẩn, hoặc tuần chưa có mục tiêu.
 * - `dat`: đủ dữ kiện, protein ngày >= mục tiêu protein, và kcal mục tiêu
 *   nằm trong khoảng [kcal_min, kcal_max] của ngày (khoảng, QT-5 — không
 *   ép thành một số, không tự đặt dải ±10% nào).
 * - `thieu`: đủ dữ kiện, có mục tiêu, nhưng protein chưa tới sàn, hoặc
 *   kcal mục tiêu nằm ngoài khoảng ngày. Kèm số thiếu/vượt cụ thể.
 * Cảnh báo kết hợp không vào validator: chỉ là lớp hiển thị, không chặn,
 * không lọc, không chấm điểm tuần.
 */
class WeekValidator
{
    /** Bữa chuẩn của một ngày trong tuần ăn: sáng, trưa, tối, ăn vặt. */
    public const SLOTS = ['sang', 'trua', 'toi', 'an-vat'];

    /** @return array<string, mixed> */
    public function validateDay(WeekPlanDay $day, WeekPlan $plan): array
    {
        $meals = $day->meals()->with('dish.nutrition')->get();
        $protein = 0.0;
        $kcalMin = 0.0;
        $kcalMax = 0.0;
        $unknown = 0;
        foreach ($meals as $meal) {
            $nutrition = $meal->dish?->nutrition;
            $servings = (float) $meal->servings;
            if ($meal->is_eat_out || $nutrition === null
                || $nutrition->protein_g === null
                || $nutrition->kcal_min === null || $nutrition->kcal_max === null
                || $nutrition->confidence === 'unknown'
                || ! empty($nutrition->missing_ingredients)
                || ! empty($nutrition->unverified_ingredients)) {
                $unknown++;
                continue;
            }
            $protein += (float) $nutrition->protein_g * $servings;
            $kcalMin += (float) $nutrition->kcal_min * $servings;
            $kcalMax += (float) $nutrition->kcal_max * $servings;
        }
        $presentSlots = $meals->pluck('slot')->all();
        $missingSlots = array_values(array_diff(self::SLOTS, $presentSlots));

        $targetProtein = $plan->target_protein_g_per_day;
        $targetKcal = $plan->target_kcal_per_day;

        $status = 'chua-du-du-lieu';
        $proteinShortfall = null;
        $kcalShortfall = null;
        $kcalExcess = null;
        if ($unknown === 0 && $missingSlots === [] && $targetProtein !== null && $targetKcal !== null) {
            $proteinShortfall = max(0, $targetProtein - (int) round($protein));
            if ($targetKcal > $kcalMax) {
                $kcalShortfall = (int) round($targetKcal - $kcalMax);
                $kcalExcess = 0;
            } elseif ($targetKcal < $kcalMin) {
                $kcalShortfall = 0;
                $kcalExcess = (int) round($kcalMin - $targetKcal);
            } else {
                $kcalShortfall = 0;
                $kcalExcess = 0;
            }
            $status = ($proteinShortfall === 0 && $kcalShortfall === 0 && $kcalExcess === 0) ? 'dat' : 'thieu';
        }

        return [
            'day_id' => $day->id,
            'date' => $day->date->toDateString(),
            'protein_g' => round($protein, 1),
            'kcal_min' => round($kcalMin),
            'kcal_max' => round($kcalMax),
            'status' => $status,
            'unknown_meals' => $unknown,
            'missing_slots' => $missingSlots,
            'target_protein_g_per_day' => $targetProtein,
            'target_kcal_per_day' => $targetKcal,
            'protein_shortfall_g' => $proteinShortfall,
            'kcal_shortfall' => $kcalShortfall,
            'kcal_excess' => $kcalExcess,
        ];
    }

    /** @return array<string, mixed> */
    public function refreshDay(WeekPlanDay $day, WeekPlan $plan): array
    {
        $result = $this->validateDay($day, $plan);
        $day->update([
            'protein_g' => $result['protein_g'],
            'kcal_min' => $result['kcal_min'],
            'kcal_max' => $result['kcal_max'],
            'status' => $result['status'],
        ]);

        return $result;
    }

    /** @return array<int, array<string, mixed>> */
    public function refreshPlan(WeekPlan $plan): array
    {
        $out = [];
        foreach ($plan->days as $day) {
            $out[] = $this->refreshDay($day, $plan);
        }

        return $out;
    }
}
