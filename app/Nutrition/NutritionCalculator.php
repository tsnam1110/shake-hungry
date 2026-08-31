<?php

declare(strict_types=1);

namespace App\Nutrition;

/**
 * Engine tính dinh dưỡng — hàm thuần tuý (CALC-001 / CALC-002).
 *
 * Đặc tả: docs/06-nutrition/CALORIE-ENGINE.md. Không HTTP, không LLM, không now(),
 * không đọc cấu hình toàn cục — cùng đầu vào luôn cho cùng đầu ra (CODING-STANDARDS §4).
 *
 * Nguồn các hằng số:
 *  - 9 kcal/g chất béo (dầu chiên): hệ số Atwater — CALORIE-ENGINE §4.
 *  - 2 kcal/g chất xơ (hiệu chỉnh thực phẩm giàu xơ): quy ước EU (Regulation 1169/2011,
 *    Annex XIV) — CALORIE-ENGINE §4b; ngưỡng xơ/carb 0,5 do dự án đặt, chưa hiệu chuẩn.
 *  - Các hệ số nới khoảng: CALORIE-ENGINE §5 — giá trị khởi đầu, hiệu chuẩn ở TEST-002.
 */
final class NutritionCalculator
{
    public const string ENGINE_VERSION = '1.0.0';

    private const array FRYING_METHODS = ['chien', 'ran', 'chien-ngap-dau'];

    /** Hệ số nới khoảng theo mapping_confidence — CALORIE-ENGINE §5. */
    private const array MAPPING_SPREAD = [
        'high' => 0.0,
        'medium' => 0.05,
        'low' => 0.15,
    ];

    private const float FRYING_MIN_FACTOR = 0.80;

    private const float FRYING_MAX_FACTOR = 1.25;

    private const float UNWEIGHED_PORTION_SPREAD = 0.15;

    private const float FAT_KCAL_PER_G = 9.0;

    private const float FIBER_KCAL_PER_G = 2.0;

    private const float FIBER_CARB_RATIO_THRESHOLD = 0.5;

    private const float MISSING_MASS_UNKNOWN_THRESHOLD = 0.30;

    /**
     * @param  list<IngredientInput>  $ingredients
     * @param  string  $cookingMethod  một trong dishes.cooking_method, hoặc 'song'
     * @param  string  $fctSource  bảng thành phần duy nhất của phép tính (QT-4),
     *                             hoặc 'mixed' — ngoại lệ có kiểm soát cho nguyên liệu
     *                             đặc thù VN (DATA-PIPELINE §7.2), kéo confidence ≤ medium
     * @param  bool  $portionWeighed  khẩu phần có cân không — ảnh hưởng khoảng và confidence
     */
    public function compute(
        array $ingredients,
        string $cookingMethod,
        string $fctSource,
        bool $portionWeighed = true,
    ): NutritionResult {
        $missing = [];
        $notes = [];
        $totalRawG = 0.0;
        $edibleG = 0.0;   // khối lượng được ăn (bỏ is_discarded), mẫu số cho tỷ lệ thiếu
        $missingG = 0.0;

        $kcalMin = 0.0;
        $kcalMax = 0.0;
        $protein = 0.0;
        $fat = 0.0;
        $carb = 0.0;
        $fiber = 0.0;
        $optionalKcal = 0.0; // phần đóng góp của nguyên liệu tuỳ chọn — chỉ nằm ở cận trên

        $worstMapping = 'high';
        $cookedG = 0.0;
        $cookedKnown = true;
        $isFried = in_array($cookingMethod, self::FRYING_METHODS, true);

        foreach ($ingredients as $ing) {
            $totalRawG += $ing->grams;

            // Khối lượng thành phẩm: tính cả nguyên liệu bị vớt bỏ (CALORIE-ENGINE §4a).
            if ($ing->isReadyToEat) {
                $cookedG += $ing->grams;
            } elseif ($ing->yieldFactor !== null) {
                $cookedG += $ing->grams * $ing->yieldFactor;
            } else {
                $cookedKnown = false;
            }

            // Vớt bỏ: tính khối lượng, KHÔNG cộng calo và macro (CALORIE-ENGINE §4a).
            if ($ing->isDiscarded) {
                continue;
            }

            $edibleG += $ing->grams;

            // QT-4: chỉ nhận dữ liệu đúng bảng thành phần yêu cầu. 'mixed' là ngoại lệ
            // có kiểm soát (DATA-PIPELINE §7.2) — nhận mọi nguồn, trả giá bằng confidence.
            $sourceAccepted = $fctSource === 'mixed'
                ? $ing->fctSource !== null
                : $ing->fctSource === $fctSource;

            if (! $sourceAccepted || $ing->kcalPer100g === null) {
                $missing[] = $ing->slug;
                $missingG += $ing->grams;

                continue; // bỏ qua khỏi phép cộng, không ước lượng thay thế (QT-1)
            }

            // Món chiên: nguyên liệu chưa có hệ số hấp thụ dầu có nguồn ⇒ missing[]
            // (CALC-002 mục 3) — không bịa hệ số (QT-1). Hệ quả: món chiên thiếu số
            // liệu dầu sẽ tụt confidence, thậm chí unknown — đó là hành vi đúng.
            if ($isFried && $ing->oilAbsorptionPct === null) {
                $missing[] = $ing->slug;
                $missingG += $ing->grams;
                $notes[] = "thieu-he-so-hap-thu-dau:{$ing->slug}";

                continue;
            }

            $ratio = $ing->grams / 100.0;
            $kcal = $ing->kcalPer100g * $ratio;
            $ingFiber = ($ing->fiberG ?? 0.0) * $ratio;
            $ingCarb = ($ing->carbG ?? 0.0) * $ratio;

            // Hiệu chỉnh thực phẩm giàu xơ — CALORIE-ENGINE §4b (mộc nhĩ khô…).
            if ($ingCarb > 0 && $ing->fiberG !== null && $ing->carbG !== null
                && ($ing->fiberG / $ing->carbG) > self::FIBER_CARB_RATIO_THRESHOLD) {
                $kcal -= $ingFiber * self::FIBER_KCAL_PER_G;
                $notes[] = "da-hieu-chinh-xo:{$ing->slug}";
            }

            $spread = self::MAPPING_SPREAD[$ing->mappingConfidence] ?? self::MAPPING_SPREAD['low'];
            $ingMin = $kcal * (1 - $spread);
            $ingMax = $kcal * (1 + $spread);

            if ($ing->isOptional) {
                // Tuỳ chọn: có thể không có mặt ⇒ cận dưới không tính, cận trên tính đủ.
                $optionalKcal += $ingMin; // để truy vết, không dùng trong tổng
                $kcalMax += $ingMax;
            } else {
                $kcalMin += $ingMin;
                $kcalMax += $ingMax;
            }

            // Macro là ước lượng trung tâm của công thức đầy đủ (gồm cả tuỳ chọn) —
            // DATA-MODEL chỉ có một cột cho mỗi macro, không có khoảng.
            $protein += ($ing->proteinG ?? 0.0) * $ratio;
            $fat += ($ing->fatG ?? 0.0) * $ratio;
            $carb += $ingCarb;
            $fiber += $ingFiber;

            $worstMapping = $this->worseOf($worstMapping, $ing->mappingConfidence);
        }

        // Không có nguyên liệu nào, hoặc thiếu ≥30% khối lượng ⇒ unknown, không trả số.
        $missingShare = $edibleG > 0 ? $missingG / $edibleG : 1.0;
        if ($edibleG <= 0 || $missingShare >= self::MISSING_MASS_UNKNOWN_THRESHOLD) {
            return new NutritionResult(
                kcalMin: null, kcalMax: null,
                proteinG: null, fatG: null, carbG: null, fiberG: null,
                confidence: 'unknown',
                missing: $missing,
                fctSource: $fctSource,
                engineVersion: self::ENGINE_VERSION,
                totalRawG: $totalRawG,
                cookedGramsEstimate: null,
                notes: $notes,
            );
        }

        // Dầu hấp thụ khi chiên — CALORIE-ENGINE §4. Nguồn sai số lớn nhất với món Việt.
        if ($isFried) {
            $oilG = 0.0;
            foreach ($ingredients as $ing) {
                if ($ing->isDiscarded || in_array($ing->slug, $missing, true)) {
                    continue;
                }
                if ($ing->oilAbsorptionPct !== null) {
                    $oilG += $ing->grams * $ing->oilAbsorptionPct / 100.0;
                }
            }
            $oilKcal = $oilG * self::FAT_KCAL_PER_G;
            $kcalMin += $oilKcal;
            $kcalMax += $oilKcal;
            $fat += $oilG;
            $kcalMin *= self::FRYING_MIN_FACTOR;
            $kcalMax *= self::FRYING_MAX_FACTOR;
        }

        if (! $portionWeighed) {
            $kcalMin *= (1 - self::UNWEIGHED_PORTION_SPREAD);
            $kcalMax *= (1 + self::UNWEIGHED_PORTION_SPREAD);
        }

        // missing[] chỉ nới cận TRÊN theo tỷ lệ khối lượng thiếu (CALORIE-ENGINE §5).
        if ($missingG > 0) {
            $kcalMax *= (1 + $missingShare);
        }

        $confidence = $this->determineConfidence(
            worstMapping: $worstMapping,
            isFried: $isFried,
            portionWeighed: $portionWeighed,
            hasMissing: $missing !== [],
            mixedSource: $fctSource === 'mixed',
        );

        return new NutritionResult(
            kcalMin: round($kcalMin, 1),
            kcalMax: round($kcalMax, 1),
            proteinG: round($protein, 1),
            fatG: round($fat, 1),
            carbG: round($carb, 1),
            fiberG: round($fiber, 1),
            confidence: $confidence,
            missing: $missing,
            fctSource: $fctSource,
            engineVersion: self::ENGINE_VERSION,
            totalRawG: round($totalRawG, 1),
            cookedGramsEstimate: $cookedKnown ? round($cookedG, 1) : null,
            notes: $notes,
        );
    }

    /** Bảng xác định confidence — CALORIE-ENGINE §6, cộng thêm ngoại lệ 'mixed' §7.2. */
    private function determineConfidence(
        string $worstMapping,
        bool $isFried,
        bool $portionWeighed,
        bool $hasMissing,
        bool $mixedSource,
    ): string {
        if ($hasMissing || $worstMapping === 'low') {
            return 'low';
        }
        if ($worstMapping === 'medium' || $isFried || ! $portionWeighed || $mixedSource) {
            return 'medium';
        }

        return 'high';
    }

    private function worseOf(string $a, string $b): string
    {
        $rank = ['high' => 0, 'medium' => 1, 'low' => 2];

        return ($rank[$b] ?? 2) > ($rank[$a] ?? 2) ? $b : $a;
    }
}
