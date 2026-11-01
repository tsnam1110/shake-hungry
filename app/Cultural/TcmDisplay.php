<?php

declare(strict_types=1);

namespace App\Cultural;

use App\Models\Dish;
use App\Models\Ingredient;
use App\Models\IngredientTcmTrait;

/**
 * Trình bày lớp tính vị cho API công khai (TCM-003, nền TCM-000).
 *
 * Mặc định HIỂN THỊ (quyết định chủ dự án 2026-11-01, đảo ngược thiết kế
 * opt-in gốc) — nhưng ngưỡng TRUNG THỰC không đổi theo quyết định đó:
 *  - Món: coverage <30% ⇒ không kết luận nào (trả null); 30–<60% ⇒ hiển
 *    thị kèm confidence bị chặn ở low; ≥60% ⇒ hiển thị đầy đủ.
 *  - Nguyên liệu: hiển thị MỌI dòng nhãn kèm nguồn — kể cả khi các nguồn
 *    mâu thuẫn (mâu thuẫn là thông tin, không phải lỗi cần giấu).
 * Diễn đạt giữ khung văn hoá thuần tuý (NGU-HANH-AM-DUONG §5); nhãn của
 * khối là đúng chữ "Tính vị:" theo chốt của chủ dự án.
 */
final class TcmDisplay
{
    /** Nhãn khối hiển thị — chốt của chủ dự án 2026-11-01. */
    public const BLOCK_LABEL = 'Tính vị:';

    /**
     * Khối tính vị của một nguyên liệu: mọi dòng nhãn, đã chuẩn hoá nhãn
     * tiếng Việt + element hiệu lực (ghi tường minh, hoặc suy từ vị).
     *
     * @return array{traits: list<array<string, mixed>>, has_conflicts: bool}|null
     */
    public static function forIngredient(Ingredient $ingredient): ?array
    {
        $traits = $ingredient->relationLoaded('tcmTraits')
            ? $ingredient->tcmTraits
            : $ingredient->tcmTraits()->get();
        if ($traits->isEmpty()) {
            return null;
        }

        return [
            'traits' => $traits->map(fn (IngredientTcmTrait $t) => self::traitRow($t))->values()->all(),
            'has_conflicts' => $traits->pluck('nature')->filter()->unique()->count() > 1,
        ];
    }

    /**
     * Khối tính vị của một món từ dish_tcm_summary. Null khi chưa có tổng
     * hợp hoặc coverage dưới ngưỡng hiển thị tối thiểu — dưới ngưỡng thì
     * im lặng, không suy đoán.
     *
     * @return array<string, mixed>|null
     */
    public static function forDish(Dish $dish): ?array
    {
        $summary = $dish->relationLoaded('tcmSummary') ? $dish->tcmSummary : $dish->tcmSummary()->first();
        if ($summary === null || $summary->coverage_pct < TcmVocabulary::DISPLAY_MIN_COVERAGE) {
            return null;
        }

        $dish->loadMissing('dishIngredients.ingredient.tcmTraits');
        $sources = $dish->dishIngredients
            ->flatMap(fn ($line) => $line->ingredient->tcmTraits)
            ->pluck('source_ref')
            ->unique()
            ->values()
            ->all();

        $nature = TcmVocabulary::natureForScore($summary->nature_score);

        return [
            'nature' => $nature,
            'nature_label' => TcmVocabulary::NATURE_LABELS[$nature],
            'nature_score' => $summary->nature_score,
            'dominant_element' => $summary->dominant_element,
            'dominant_element_label' => $summary->dominant_element !== null
                ? (TcmVocabulary::ELEMENT_LABELS[$summary->dominant_element] ?? $summary->dominant_element)
                : null,
            'coverage_pct' => $summary->coverage_pct,
            'confidence' => $summary->confidence,
            // 30–<60%: kết luận chỉ ở mức tham khảo — giao diện phải nói rõ.
            'is_partial' => $summary->coverage_pct < TcmVocabulary::FULL_DISPLAY_COVERAGE,
            'has_conflicts' => $summary->has_conflicts,
            'sources' => $sources,
            'cooking_adjustment' => TcmVocabulary::cookingAdjustment($dish->cooking_method),
        ];
    }

    /** @return array<string, mixed> */
    public static function traitRow(IngredientTcmTrait $trait): array
    {
        // Element hiệu lực theo căn cứ của dòng (TCM-001a): ghi tường minh
        // thì dùng nguyên văn; trống thì suy theo đúng hệ basis đã khai —
        // basis='direct' mà trống thì để trống, không suy thay nguồn.
        $basis = $trait->element_basis ?? 'flavor';
        $element = $trait->element ?? match ($basis) {
            'flavor' => TcmVocabulary::elementForFlavor($trait->flavor),
            'nature' => TcmVocabulary::elementForNature($trait->nature),
            default => null,
        };

        return [
            'nature' => $trait->nature,
            'nature_label' => $trait->nature !== null ? (TcmVocabulary::NATURE_LABELS[$trait->nature] ?? $trait->nature) : null,
            'flavor' => $trait->flavor,
            'flavor_label' => $trait->flavor !== null ? (TcmVocabulary::FLAVOR_LABELS[$trait->flavor] ?? $trait->flavor) : null,
            'element' => $element,
            'element_label' => $element !== null ? (TcmVocabulary::ELEMENT_LABELS[$element] ?? $element) : null,
            'element_basis' => $basis,
            'source_ref' => $trait->source_ref,
            'confidence' => $trait->confidence,
            'usage_restricted' => $trait->usage_restricted,
            'usage_note' => $trait->usage_note,
        ];
    }
}
