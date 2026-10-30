<?php

declare(strict_types=1);

namespace App\Recommendation;

use App\Models\Dish;
use App\Models\RecommendationLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Engine gợi ý 3 tầng cho nút Lắc (REC-001, đặc tả RECOMMENDATION-ENGINE):
 *   Tầng 1 — lọc cứng: dị ứng (KHÔNG BAO GIỜ nới lỏng), chế độ ăn, vùng miền.
 *   Tầng 2 — loại trừ: N món vừa ăn gần nhất + cùng họ biến thể (parent_dish_id).
 *   Tầng 3 — chấm điểm: bữa 0,40 + calo 0,25 + đa dạng 0,25 + dữ liệu 0,10,
 *            chọn ngẫu nhiên có trọng số trong nhóm 20% cao nhất theo seed.
 *
 * Điều kiện vào kho gợi ý: món có bản ghi dish_nutrition do engine tính
 * (QT-5 — không có số thì không gợi ý kèm số). Khi chủ dự án duyệt đăng
 * (status='published') đủ nhiều, bật RECOMMEND_STRICT_PUBLISHED để siết về
 * đúng đặc tả gốc. Món còn thiếu nguyên liệu (missing_ingredients) bị loại
 * với người dùng có khai báo dị ứng.
 */
class Recommender
{
    public const WEIGHTS = ['slot' => 0.40, 'kcal' => 0.25, 'diversity' => 0.25, 'data' => 0.10];

    /**
     * @param  array{slot: string, target_kcal?: ?float, region_id?: ?int, vegetarian?: bool,
     *               allergens?: string[], recent_dish_ids?: int[], shake_index?: int,
     *               user_id?: ?int, strict_published?: bool}  $input
     * @return array{dish: ?Dish, seed: string, relaxed_level: int, pool_size: int, suggestion?: string}
     */
    public function shake(array $input): array
    {
        $slot = $input['slot'];
        $allergens = $input['allergens'] ?? [];
        $recentIds = $input['recent_dish_ids'] ?? [];
        $seed = hash('sha256', implode('|', [
            $input['user_id'] ?? 'guest',
            now()->toDateString(),
            $slot,
            (string) ($input['shake_index'] ?? 0),
        ]));

        // Nới lỏng dần: 0 = đầy đủ, 1 = giảm cửa sổ loại trừ 10→3,
        // 2 = bỏ lọc vùng, 3 = bỏ lọc chế độ ăn. Dị ứng không nới ở bất kỳ mức nào.
        for ($level = 0; $level <= 3; $level++) {
            $pool = $this->pool($input, $level, $allergens);
            $excluded = $level === 0 ? array_slice($recentIds, -10) : array_slice($recentIds, -3);
            $candidates = $pool->reject(fn (Dish $d) => in_array($d->id, $excluded, true)
                || ($d->parent_dish_id !== null && in_array($d->parent_dish_id, $excluded, true)));

            if ($candidates->isNotEmpty()) {
                $dish = $this->pick($candidates, $input, $recentIds, $seed);
                RecommendationLog::create([
                    'user_id' => $input['user_id'] ?? null,
                    'slot' => $slot,
                    'seed' => $seed,
                    'dish_id' => $dish->id,
                    'relaxed_level' => $level,
                    'context' => ['pool_size' => $candidates->count(), 'allergens' => $allergens],
                ]);
                Log::info('shake', ['seed' => $seed, 'dish' => $dish->slug, 'relaxed' => $level]);

                return ['dish' => $dish, 'seed' => $seed, 'relaxed_level' => $level, 'pool_size' => $candidates->count()];
            }
        }

        RecommendationLog::create([
            'user_id' => $input['user_id'] ?? null,
            'slot' => $slot, 'seed' => $seed, 'dish_id' => null,
            'relaxed_level' => 3, 'context' => ['pool_size' => 0, 'allergens' => $allergens],
        ]);

        return [
            'dish' => null, 'seed' => $seed, 'relaxed_level' => 3, 'pool_size' => 0,
            'suggestion' => 'Không còn món phù hợp — hãy nới bộ lọc (bỏ bớt vùng miền/chế độ ăn) hoặc lắc sang bữa khác.',
        ];
    }

    private function pool(array $input, int $level, array $allergens): Collection
    {
        $query = Dish::query()
            ->with(['names' => fn ($q) => $q->where('is_primary', true), 'region:id,name', 'nutrition', 'mealSlots'])
            ->whereHas('nutrition');
        // Bữa chính không gợi ý món gia vị/khẩu phần quá nhỏ (nước mắm, muối tôm…)
        // — chúng là đồ chấm kèm, không phải món ăn. Bữa ăn vặt không áp quy tắc này.
        if (in_array($input['slot'], ['sang', 'trua', 'chieu', 'toi'], true)) {
            $query->where(fn ($q) => $q->where('serving_grams', '>=', 80)->orWhereNull('serving_grams'));
        }
        if (($input['strict_published'] ?? false) === true) {
            $query->where('status', 'published');
        }
        if ($level < 2 && ! empty($input['region_id'])) {
            $query->where('region_id', $input['region_id']);
        }
        if (! empty($input['eat_out'])) {
            $query->where('is_eat_out', true);
        }
        if (! empty($input['home_cook'])) {
            $query->where('is_home_cook', true);
        }
        $dishes = $query->get();

        // Lọc theo thành phần (dị ứng/chay) ở tầng PHP: tập dữ liệu nhỏ, rõ ràng.
        return $dishes->filter(function (Dish $dish) use ($input, $level, $allergens) {
            if ($allergens !== []) {
                // Món còn thiếu dữ liệu nguyên liệu ⇒ không an toàn cho người dị ứng.
                if (! empty($dish->nutrition?->missing_ingredients)) {
                    return false;
                }
                $slugs = $dish->dishIngredients()->with('ingredient:id,slug')->get()
                    ->pluck('ingredient.slug')->all();
                if (array_intersect($allergens, $slugs) !== []) {
                    return false;
                }
            }
            if ($level < 3 && ($input['vegetarian'] ?? false)) {
                $hasMeat = $dish->dishIngredients()->with('ingredient:id,category')->get()
                    ->contains(fn ($di) => in_array($di->ingredient->category, ['thit', 'thuy-san'], true));
                if ($hasMeat) {
                    return false;
                }
            }

            return true;
        });
    }

    private function pick(Collection $candidates, array $input, array $recentIds, string $seed): Dish
    {
        $lastDish = $recentIds !== [] ? Dish::with('dishIngredients')->find(end($recentIds)) : null;
        $scored = $candidates->map(fn (Dish $d) => ['dish' => $d, 'score' => $this->score($d, $input, $lastDish)])
            ->sortByDesc('score')->values();

        $topCount = max(1, (int) ceil($scored->count() * 0.2));
        $top = $scored->take($topCount);

        // Ngẫu nhiên có trọng số, tất định theo seed (dựng lại được — DoD REC-001).
        mt_srand((int) hexdec(substr($seed, 0, 8)));
        $total = $top->sum('score');
        $roll = (mt_rand() / mt_getrandmax()) * max($total, 0.0001);
        foreach ($top as $row) {
            $roll -= $row['score'];
            if ($roll <= 0) {
                return $row['dish'];
            }
        }

        return $top->first()['dish'];
    }

    private function score(Dish $dish, array $input, ?Dish $lastDish): float
    {
        // Phù hợp bữa
        $slots = $dish->mealSlots->pluck('slot')->all();
        $slotScore = $slots === [] ? 0.5 : (in_array($input['slot'], $slots, true) ? 1.0 : 0.2);

        // Phù hợp calo (trung điểm khoảng kcal so với mục tiêu; chưa có mục tiêu ⇒ trung tính)
        $n = $dish->nutrition;
        if (! empty($input['target_kcal']) && $n !== null) {
            $mid = ($n->kcal_min + $n->kcal_max) / 2;
            $kcalScore = max(0.0, 1.0 - abs($mid - $input['target_kcal']) / max($input['target_kcal'], 1));
        } else {
            $kcalScore = 0.5;
        }

        // Đa dạng so với món gần nhất (heuristic thay embedding — REC-001 ghi chú)
        if ($lastDish !== null) {
            $diversity = 1.0;
            if ($dish->cooking_method !== null && $dish->cooking_method === $lastDish->cooking_method) {
                $diversity -= 0.5;
            }
            $mainOf = fn (Dish $d) => $d->dishIngredients->sortByDesc('quantity')->first()?->ingredient_id;
            if ($mainOf($dish) !== null && $mainOf($dish) === $mainOf($lastDish)) {
                $diversity -= 0.5;
            }
        } else {
            $diversity = 0.7;
        }

        // Đầy đủ dữ liệu
        $confFactor = match ($n?->confidence) { 'high' => 1.0, 'medium' => 0.7, 'low' => 0.4, default => 0.2 };
        $dataScore = 0.5 * $confFactor + 0.5 * ($dish->completeness_level >= 2 ? 1.0 : 0.5);

        $score = self::WEIGHTS['slot'] * $slotScore
            + self::WEIGHTS['kcal'] * $kcalScore
            + self::WEIGHTS['diversity'] * $diversity
            + self::WEIGHTS['data'] * $dataScore;

        return max($score, 0.01);
    }
}
