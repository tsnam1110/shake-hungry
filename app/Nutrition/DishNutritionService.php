<?php

declare(strict_types=1);

namespace App\Nutrition;

use App\Models\Dish;
use App\Models\DishIngredient;
use App\Models\DishNutrition;
use Illuminate\Support\Facades\Date;

/**
 * Tầng "không thuần tuý" quanh NutritionCalculator: đọc model, quy đổi đơn vị
 * đời thường → gram, gọi engine, và ghi kết quả vào dish_nutrition.
 *
 * Chỉ tầng này được chạm database/thời gian — engine giữ thuần tuý (CALC-001).
 */
final class DishNutritionService
{
    public function __construct(private readonly NutritionCalculator $calculator) {}

    /**
     * Một bảng thành phần duy nhất cho một phép tính (QT-4): mọi nguyên liệu cùng
     * một fct_source thì dùng nguồn đó; lẫn nguồn ⇒ 'mixed' (ngoại lệ có kiểm soát
     * DATA-PIPELINE §7.2 — engine hạ confidence xuống tối đa medium).
     */
    public function pickFctSource(Dish $dish): string
    {
        $sources = $dish->dishIngredients()
            ->with('ingredient.referenceFood')
            ->get()
            ->map(function (DishIngredient $di) {
                if ($di->ingredient->referenceFood !== null) {
                    return $di->ingredient->referenceFood->fct_source;
                }

                return $di->ingredient->nutrition_snapshot['fct_source'] ?? null;
            })
            ->filter()
            ->unique()
            ->values();

        return $sources->count() === 1 ? (string) $sources->first() : 'mixed';
    }

    /**
     * Nguồn dinh dưỡng của một nguyên liệu, theo đúng thứ tự ưu tiên DATA-MODEL §3.1:
     * reference_foods là nguồn sự thật lâu dài; nutrition_snapshot chỉ dùng khi
     * chưa ánh xạ được (data/reference/ chưa nhập) — không bao giờ dùng cả hai.
     *
     * @return array{fct: ?string, kcal: ?float, protein: ?float, fat: ?float, carb: ?float, fiber: ?float}
     */
    private function nutritionFor(DishIngredient $di): array
    {
        $ing = $di->ingredient;

        if ($ing->reference_food_id !== null && $ing->referenceFood !== null) {
            $ref = $ing->referenceFood;

            return [
                'fct' => $ref->fct_source,
                'kcal' => $ref->kcal_per_100g,
                'protein' => $ref->protein_g,
                'fat' => $ref->fat_g,
                'carb' => $ref->carb_g,
                'fiber' => $ref->fiber_g,
            ];
        }

        $snap = $ing->nutrition_snapshot;
        if (is_array($snap) && ($snap['kcal_per_100g'] ?? null) !== null) {
            return [
                'fct' => $snap['fct_source'] ?? null,
                'kcal' => isset($snap['kcal_per_100g']) ? (float) $snap['kcal_per_100g'] : null,
                'protein' => isset($snap['protein_g']) ? (float) $snap['protein_g'] : null,
                'fat' => isset($snap['fat_g']) ? (float) $snap['fat_g'] : null,
                'carb' => isset($snap['carb_g']) ? (float) $snap['carb_g'] : null,
                'fiber' => isset($snap['fiber_g']) ? (float) $snap['fiber_g'] : null,
            ];
        }

        return ['fct' => null, 'kcal' => null, 'protein' => null, 'fat' => null, 'carb' => null, 'fiber' => null];
    }

    /** Quy đổi (quantity, unit_name) → gram. Null = không quy đổi được ⇒ missing. */
    public function resolveGrams(DishIngredient $di): ?float
    {
        $ing = $di->ingredient;
        $unit = mb_strtolower(trim($di->unit_name));

        if ($unit === 'g') {
            return $di->quantity;
        }
        if ($unit === 'kg') {
            return $di->quantity * 1000.0;
        }
        if ($unit === 'ml') {
            return $ing->density_g_per_ml !== null ? $di->quantity * $ing->density_g_per_ml : null;
        }
        if ($unit === 'l') {
            return $ing->density_g_per_ml !== null ? $di->quantity * 1000.0 * $ing->density_g_per_ml : null;
        }

        $row = $ing->units->firstWhere(fn ($u) => mb_strtolower(trim($u->unit_name)) === $unit);

        return $row !== null ? $di->quantity * $row->grams : null;
    }

    public function compute(Dish $dish, string $fctSource, bool $portionWeighed = false): NutritionResult
    {
        $dish->loadMissing('dishIngredients.ingredient.units', 'dishIngredients.ingredient.referenceFood');

        $inputs = [];
        foreach ($dish->dishIngredients as $di) {
            $grams = $this->resolveGrams($di);
            $n = $this->nutritionFor($di);

            // DATA-008 / CALORIE-ENGINE §3b: khối lượng mua ở chợ × phần ăn được,
            // ÁP TRƯỚC khi tra dinh dưỡng. Bỏ qua khi công thức đã ghi khối lượng
            // ăn được (is_edible_weight) hoặc nguyên liệu không có hệ số.
            $ediblePct = $di->ingredient->edible_portion_pct;
            if ($grams !== null && $ediblePct !== null && ! $di->is_edible_weight) {
                $grams *= $ediblePct / 100.0;
            }

            $inputs[] = new IngredientInput(
                slug: $di->ingredient->slug,
                grams: $grams ?? 0.0,
                isOptional: $di->is_optional,
                isDiscarded: $di->is_discarded,
                mappingConfidence: $di->ingredient->mapping_confidence,
                // Không quy đổi được gram ⇒ coi như không tra được (missing).
                fctSource: $grams === null ? null : $n['fct'],
                kcalPer100g: $n['kcal'],
                proteinG: $n['protein'],
                fatG: $n['fat'],
                carbG: $n['carb'],
                fiberG: $n['fiber'],
                isReadyToEat: $di->ingredient->is_ready_to_eat,
                yieldFactor: $di->ingredient->default_yield_factor,
                oilAbsorptionPct: $di->ingredient->oil_absorption_pct,
            );
        }

        return $this->calculator->compute(
            ingredients: $inputs,
            cookingMethod: $dish->cooking_method ?? 'song',
            fctSource: $fctSource,
            portionWeighed: $portionWeighed,
        );
    }

    /**
     * Tính và ghi vào dish_nutrition với source='computed'.
     * confidence='unknown' ⇒ XOÁ bản ghi cũ nếu có và không ghi gì —
     * món hiển thị "chưa đủ dữ liệu" (QT-5).
     */
    public function computeAndStore(Dish $dish, string $fctSource): ?DishNutrition
    {
        $result = $this->compute($dish, $fctSource);

        if (! $result->hasNumbers()) {
            DishNutrition::where('dish_id', $dish->id)->delete();

            return null;
        }

        // Cờ "chưa kiểm định" (quyết định chủ dự án 2026-10-31): nguyên liệu dùng
        // nguồn THỨ CẤP (SourceTier) mà thực sự tham gia phép tính — không nằm
        // trong missing[] — được ghi lại để giao diện cảnh báo người dùng.
        $unverified = $dish->dishIngredients
            ->reject(fn ($di) => in_array($di->ingredient->slug, $result->missing, true))
            ->filter(fn ($di) => SourceTier::isSecondary($this->nutritionFor($di)['fct']))
            ->map(fn ($di) => $di->ingredient->slug)
            ->unique()
            ->values()
            ->all();

        DishNutrition::where('dish_id', $dish->id)->delete();

        return DishNutrition::create([
            'dish_id' => $dish->id,
            'basis' => 'per_serving',
            'kcal_min' => $result->kcalMin,
            'kcal_max' => $result->kcalMax,
            'protein_g' => $result->proteinG,
            'fat_g' => $result->fatG,
            'carb_g' => $result->carbG,
            'fiber_g' => $result->fiberG,
            'confidence' => $result->confidence,
            'fct_source' => $result->fctSource,
            'missing_ingredients' => $result->missing,
            'unverified_ingredients' => $unverified,
            'computed_at' => Date::now(),
            'engine_version' => $result->engineVersion,
            'source' => 'computed',
        ]);
    }
}
