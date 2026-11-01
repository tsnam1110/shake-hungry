<?php

declare(strict_types=1);

namespace App\Cultural;

use App\Models\Dish;
use App\Models\DishTcmSummary;
use App\Models\IngredientTcmTrait;
use App\Nutrition\DishNutritionService;

/**
 * Engine tổng hợp tính vị từ nguyên liệu lên món (TCM-002) — tầng "không
 * thuần tuý" quanh TcmVocabulary, cùng khuôn với DishNutritionService:
 * đọc model, quy đổi khối lượng bằng CHÍNH hàm resolveGrams của engine
 * dinh dưỡng (một định nghĩa khối lượng duy nhất cho cả hai engine),
 * tính và ghi vào dish_tcm_summary. Kết quả là dữ liệu DẪN XUẤT — không
 * bao giờ nhập tay, không xuất ra data/ (QT-3).
 *
 * Quy tắc tổng hợp (NGU-HANH-AM-DUONG §4 + các quyết định TCM-000):
 *  - Chỉ dòng nguyên liệu KHÔNG bị bỏ đi (is_discarded = false) tham gia;
 *    dòng tuỳ chọn vẫn tính (nó là một phần của món như được biên soạn).
 *  - Nguyên liệu "có nhãn" = có ≥1 dòng trait với nature khác null.
 *    Điểm tính của nguyên liệu = TRUNG BÌNH điểm của các dòng nature —
 *    mỗi nguồn một phiếu, không ép chọn khi mâu thuẫn; mâu thuẫn được
 *    ghi vào has_conflicts và hạ confidence.
 *  - nature_score = Σ(g_i × điểm_i) / Σ(g_i của nguyên liệu có nhãn)
 *    + hệ số chế biến (quy ước dự án), kẹp trong [−2, +2].
 *  - coverage_pct = Σ(g có nhãn) / Σ(g toàn món) × 100. Dòng không quy
 *    đổi được gram bị loại khỏi CẢ tử và mẫu (không có khối lượng thì
 *    không trọng số được) — engine dinh dưỡng cũng coi chúng là "thiếu".
 *  - dominant_element: mỗi nguyên liệu có nhãn bỏ phiếu bằng khối lượng
 *    của nó; nhiều hành khác nhau trong cùng nguyên liệu thì chia đều
 *    khối lượng cho các hành phân biệt; hoà phiếu ⇒ null. CHỈ dòng nhãn
 *    basis='flavor' mới bỏ phiếu (TCM-001a): dòng basis='nature'/'direct'
 *    mang element của một hệ ánh xạ khác — trộn vào cùng cuộc bỏ phiếu
 *    sẽ cho kết quả không truy vết được; chúng vẫn hiển thị ở tầng
 *    nguyên liệu và vẫn tham gia điểm tính/độ tin cậy như thường.
 *  - confidence: mắt xích yếu nhất trong các dòng trait tham gia, rồi áp
 *    trần — coverage <60 ⇒ tối đa low; có mâu thuẫn ⇒ tối đa low; và
 *    trần tuyệt đối ở cấp MÓN là 'medium' (TCM-001a): hệ số chế biến
 *    cộng vào nature_score là quy ước của dự án, không có nguồn định
 *    lượng, nên kết luận cấp món không bao giờ được 'high' — thang
 *    'high' chỉ dành cho nhãn nguyên liệu có nguồn chính thức.
 *  - Món không có nguyên liệu có nhãn nào ⇒ KHÔNG có dòng summary
 *    (xoá dòng cũ nếu có) — không bịa một con số 0 trung tính.
 */
final class TcmAggregator
{
    public function __construct(private readonly DishNutritionService $nutrition) {}

    public function summarize(Dish $dish): ?DishTcmSummary
    {
        $dish->loadMissing('dishIngredients.ingredient.units', 'dishIngredients.ingredient.tcmTraits');

        $totalGrams = 0.0;
        $labeledGrams = 0.0;
        $weightedPoints = 0.0;
        /** @var array<string, float> $elementVotes */
        $elementVotes = [];
        $hasConflicts = false;
        $weakestConfidence = null;
        $confidenceRank = ['high' => 3, 'medium' => 2, 'low' => 1];

        foreach ($dish->dishIngredients as $line) {
            if ($line->is_discarded) {
                continue;
            }
            $grams = $this->nutrition->resolveGrams($line);
            if ($grams === null || $grams <= 0) {
                continue;
            }
            $totalGrams += $grams;

            $traits = $line->ingredient->tcmTraits;
            $natureRows = $traits->filter(fn (IngredientTcmTrait $t) => $t->nature !== null);
            if ($natureRows->isEmpty()) {
                continue;
            }
            $labeledGrams += $grams;

            $points = $natureRows->avg(fn (IngredientTcmTrait $t) => TcmVocabulary::NATURE_POINTS[$t->nature]);
            $weightedPoints += $grams * $points;

            if ($natureRows->pluck('nature')->unique()->count() > 1) {
                $hasConflicts = true;
            }
            foreach ($natureRows as $row) {
                $rank = $confidenceRank[$row->confidence] ?? 1;
                if ($weakestConfidence === null || $rank < $confidenceRank[$weakestConfidence]) {
                    $weakestConfidence = $row->confidence;
                }
            }

            // Phiếu hành: chỉ dòng basis='flavor' (mặc định) — element ghi
            // tường minh trên dòng đó đã được V20 kiểm khớp ánh xạ theo vị.
            $elements = $traits
                ->filter(fn (IngredientTcmTrait $t) => ($t->element_basis ?? 'flavor') === 'flavor')
                ->map(fn (IngredientTcmTrait $t) => $t->element ?? TcmVocabulary::elementForFlavor($t->flavor))
                ->filter()
                ->unique()
                ->values();
            if ($elements->isNotEmpty()) {
                $share = $grams / $elements->count();
                foreach ($elements as $element) {
                    $elementVotes[$element] = ($elementVotes[$element] ?? 0.0) + $share;
                }
            }
        }

        if ($labeledGrams <= 0 || $totalGrams <= 0) {
            DishTcmSummary::where('dish_id', $dish->id)->delete();

            return null;
        }

        $coverage = round($labeledGrams / $totalGrams * 100, 2);
        $score = $weightedPoints / $labeledGrams + TcmVocabulary::cookingAdjustment($dish->cooking_method);
        $score = round(max(-2.0, min(2.0, $score)), 2);

        $dominantElement = null;
        if ($elementVotes !== []) {
            $max = max($elementVotes);
            $winners = array_keys(array_filter($elementVotes, fn ($v) => abs($v - $max) < 1e-9));
            $dominantElement = count($winners) === 1 ? $winners[0] : null;
        }

        $confidence = $weakestConfidence ?? 'low';
        if ($coverage < TcmVocabulary::LEVEL3_MIN_COVERAGE || $hasConflicts) {
            $confidence = 'low';
        }
        // Trần tuyệt đối cấp món = medium (TCM-001a, xem docblock).
        if ($confidence === 'high') {
            $confidence = 'medium';
        }

        return DishTcmSummary::updateOrCreate(
            ['dish_id' => $dish->id],
            [
                'nature_score' => $score,
                'dominant_element' => $dominantElement,
                'coverage_pct' => $coverage,
                'confidence' => $confidence,
                'has_conflicts' => $hasConflicts,
                'computed_at' => now(),
            ],
        );
    }

    /**
     * Tính lại toàn bộ món. Idempotent: chạy lại trên cùng dữ liệu cho cùng
     * giá trị (chỉ computed_at thay đổi).
     *
     * @return array{computed: int, cleared: int}
     */
    public function recomputeAll(): array
    {
        $computed = 0;
        $cleared = 0;
        foreach (Dish::query()->orderBy('id')->get() as $dish) {
            $before = DishTcmSummary::where('dish_id', $dish->id)->exists();
            $summary = $this->summarize($dish);
            if ($summary !== null) {
                $computed++;
            } elseif ($before) {
                $cleared++;
            }
        }

        return ['computed' => $computed, 'cleared' => $cleared];
    }
}
