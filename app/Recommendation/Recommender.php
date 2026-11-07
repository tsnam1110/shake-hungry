<?php

declare(strict_types=1);

namespace App\Recommendation;

use App\Models\Dish;
use App\Models\RecommendationLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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
 *
 * QUY ĐỊNH CỒN (quyết định chủ dự án 2026-11-04, bước 1/4): kho Lắc chỉ
 * gồm Dish — Drink (kể cả cocktail và mọi mục alcohol=true) không bao
 * giờ nằm trong pool, mà chỉ là thư viện tra cứu, cho tới khi có lọc
 * tuổi (bước 2). Đây là tách biệt theo model, không phải lọc theo
 * nguyên liệu: món ăn nấu với rượu (ví dụ bò sốt vang) vẫn là Dish và
 * không thuộc quy định này.
 * REC-DRINK-001 (2026-11-07): nước không cồn là lựa chọn KÈM riêng qua
 * DrinkRecommender, không nhập Drink vào pool Dish; đồ có cồn vẫn bị loại.
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

    /** REC-003: các loại món KHÔNG thuộc mâm cơm (là bữa riêng hoặc đồ chấm kèm). */
    private const TRAY_EXCLUDED_LOAI = ['com', 'xoi', 'banh', 'bun-pho-mi', 'che-trang-mieng', 'nuoc-cham', 'lau', 'chao-sup'];

    /** REC-003: vai trò các món trong mâm theo số món — mâm chuẩn 3 món = chính + canh + rau. */
    private const TRAY_ROLES = [
        1 => ['chinh'],
        2 => ['chinh', 'canh'],
        3 => ['chinh', 'canh', 'rau'],
        4 => ['chinh', 'canh', 'rau', 'phu'],
    ];

    public const TRAY_ROLE_LABELS = [
        'chinh' => 'Món chính',
        'canh' => 'Canh',
        'rau' => 'Rau · món kèm',
        'phu' => 'Món phụ',
    ];

    /** Tỷ trọng kcal mục tiêu theo vai trò — chỉ là gợi ý cho khâu chấm điểm. */
    private const TRAY_KCAL_SHARE = ['chinh' => 0.52, 'canh' => 0.16, 'rau' => 0.14, 'phu' => 0.18];

    /**
     * REC-004 "Bữa cơm bình dân": ngưỡng SUY từ chính kho dữ liệu (không gán
     * tay từng món) — mọi nguyên liệu của món phải xuất hiện trong ít nhất
     * ngần này món của kho (phổ biến = dễ kiếm, giá bình dân), và cách làm
     * không quá ngần này bước (đơn giản, nấu nhanh hằng ngày). Đo trên kho
     * 657 món (2026-10-31): 212/312 món đủ điều kiện mâm đạt cả hai ngưỡng.
     */
    private const BINH_DAN_MIN_INGREDIENT_FREQ = 4;

    private const BINH_DAN_MAX_STEPS = 8;

    /** @var array<int, int>|null ingredient_id => số món trong kho dùng tới */
    private ?array $ingredientFreqCache = null;

    /**
     * REC-003: lắc MÂM CƠM tự nấu — chọn 1–4 món (không tính cơm) theo vai trò
     * của mâm cơm Việt, dùng chung kho lọc cứng của REC-001: dị ứng không bao
     * giờ nới, nới dần vùng miền → chế độ ăn khi thiếu món như lắc đơn.
     *
     * @param  array<string, mixed>  $input  như shake(), cộng dish_count
     * @return array{dishes: array<int, array{dish: Dish, role: string}>, seed: string, relaxed_level: int, pool_size: int, suggestion?: string}
     */
    public function shakeTray(array $input): array
    {
        $count = max(1, min(4, (int) ($input['dish_count'] ?? 3)));
        // REC-004: tuỳ chọn "Bữa cơm bình dân" — lọc thêm trên kho mâm cơm.
        $binhDan = (bool) ($input['binh_dan'] ?? false);
        $input['home_cook'] = true;
        $allergens = $input['allergens'] ?? [];
        $recentIds = $input['recent_dish_ids'] ?? [];
        $rolesWanted = self::TRAY_ROLES[$count];
        $seed = hash('sha256', implode('|', [
            $input['user_id'] ?? 'guest',
            now()->toDateString(),
            $input['slot'],
            'tray',
            (string) $count,
            (string) ($input['shake_index'] ?? 0),
        ]));

        $best = [];
        $bestLevel = 3;
        $bestPoolSize = 0;
        for ($level = 0; $level <= 3; $level++) {
            $pool = $this->pool($input, $level, $allergens, tray: true)
                ->filter(fn (Dish $d) => $this->trayEligible($d));
            if ($binhDan) {
                // Lọc bình dân KHÔNG nới theo level: đó là lựa chọn tường minh
                // của người dùng; thiếu món thì trả mâm thiếu + gợi ý trung thực.
                $pool = $pool->filter(fn (Dish $d) => $this->binhDanEligible($d));
            }
            $excluded = $level === 0 ? array_slice($recentIds, -10) : array_slice($recentIds, -3);
            $picked = [];
            $pickedIds = [];
            $pickedMainIds = [];
            foreach ($rolesWanted as $i => $role) {
                $candidates = $pool->reject(fn (Dish $d) => in_array($d->id, $pickedIds, true)
                    || in_array($d->id, $excluded, true)
                    || ($d->parent_dish_id !== null && in_array($d->parent_dish_id, [...$excluded, ...$pickedIds], true)));
                // Trong cùng một mâm, tránh hai món trùng nguyên liệu chính
                // (ưu tiên mềm: chỉ áp khi vai trò vẫn còn lựa chọn khác).
                $diverse = $candidates->reject(fn (Dish $d) => in_array($this->mainIngredientId($d), $pickedMainIds, true));
                $rolePool = $diverse->filter(fn (Dish $d) => $this->roleMatches($this->dishRole($d), $role));
                if ($rolePool->isEmpty()) {
                    $rolePool = $candidates->filter(fn (Dish $d) => $this->roleMatches($this->dishRole($d), $role));
                }
                if ($rolePool->isEmpty()) {
                    // Vai trò này thiếu món trong kho hiện tại → lấp bằng món hợp lệ bất kỳ còn lại.
                    $rolePool = $diverse->isNotEmpty() ? $diverse : $candidates;
                }
                if ($rolePool->isEmpty()) {
                    break;
                }
                $pickInput = $input;
                if (! empty($input['target_kcal'])) {
                    $pickInput['target_kcal'] = $input['target_kcal'] * (self::TRAY_KCAL_SHARE[$role] ?? 0.25);
                }
                $dish = $this->pick($rolePool, $pickInput, [...$recentIds, ...$pickedIds], $seed.'|'.$i);
                $picked[] = ['dish' => $dish, 'role' => $role];
                $pickedIds[] = $dish->id;
                $mainId = $this->mainIngredientId($dish);
                if ($mainId !== null) {
                    $pickedMainIds[] = $mainId;
                }
            }
            if (count($picked) > count($best)) {
                $best = $picked;
                $bestLevel = $level;
                $bestPoolSize = $pool->count();
            }
            if (count($picked) === $count) {
                break;
            }
        }

        foreach ($best as $row) {
            RecommendationLog::create([
                'user_id' => $input['user_id'] ?? null,
                'slot' => $input['slot'],
                'seed' => $seed,
                'dish_id' => $row['dish']->id,
                'relaxed_level' => $bestLevel,
                'context' => ['pool_size' => $bestPoolSize, 'allergens' => $allergens, 'mode' => 'home', 'role' => $row['role'], 'dish_count' => $count, 'binh_dan' => $binhDan],
            ]);
        }
        Log::info('shake-tray', ['seed' => $seed, 'dishes' => array_map(fn ($r) => $r['dish']->slug, $best), 'relaxed' => $bestLevel]);

        $result = ['dishes' => $best, 'seed' => $seed, 'relaxed_level' => $bestLevel, 'pool_size' => $bestPoolSize, 'binh_dan' => $binhDan];
        if ($best === []) {
            $result['suggestion'] = $binhDan
                ? 'Chưa gom được mâm cơm bình dân phù hợp — thử nới bộ lọc (trừ dị ứng) hoặc tắt "Bữa cơm bình dân" để xem thêm món khác.'
                : 'Không còn món tự nấu phù hợp — hãy nới bộ lọc (bỏ bớt vùng miền/chế độ ăn) hoặc lắc sang bữa khác.';
        } elseif (count($best) < $count) {
            $result['suggestion'] = $binhDan
                ? 'Kho món bình dân phù hợp hiện chỉ gom được '.count($best).'/'.$count.' món cho mâm này — nới bộ lọc hoặc tắt "Bữa cơm bình dân" để gom đủ mâm.'
                : 'Kho món tự nấu phù hợp hiện chỉ gom được '.count($best).'/'.$count.' món cho mâm này.';
        }

        return $result;
    }

    /**
     * REC-005: kho ứng viên cho MỘT vị trí trong mâm (phục vụ nút "Đổi món") —
     * dùng đúng đường lọc của shakeTray: dị ứng là lọc cứng không bao giờ nới,
     * "bình dân" không nới, vùng/chế độ ăn nới dần theo level khi thiếu món.
     * Loại trừ các món đang có trong mâm và họ biến thể của chúng.
     *
     * @param  array<string, mixed>  $input
     * @param  int[]  $excludeDishIds
     */
    public function trayRolePool(array $input, string $role, array $excludeDishIds): Collection
    {
        $input['home_cook'] = true;
        $allergens = $input['allergens'] ?? [];
        $binhDan = (bool) ($input['binh_dan'] ?? false);
        // Tham số GET đến dưới dạng chuỗi — chuẩn hoá về int để so khớp chặt.
        $excludeDishIds = array_map('intval', $excludeDishIds);
        $excludeParents = Dish::whereIn('id', $excludeDishIds)->pluck('parent_dish_id')->filter()->all();
        for ($level = 0; $level <= 3; $level++) {
            $pool = $this->pool($input, $level, $allergens, tray: true)
                ->filter(fn (Dish $d) => $this->trayEligible($d));
            if ($binhDan) {
                $pool = $pool->filter(fn (Dish $d) => $this->binhDanEligible($d));
            }
            $candidates = $pool->reject(fn (Dish $d) => in_array($d->id, $excludeDishIds, true)
                || in_array($d->id, $excludeParents, true)
                || ($d->parent_dish_id !== null && in_array($d->parent_dish_id, [...$excludeDishIds, ...$excludeParents], true)));
            $rolePool = $candidates->filter(fn (Dish $d) => $this->roleMatches($this->dishRole($d), $role));
            if ($rolePool->isNotEmpty()) {
                return $rolePool;
            }
        }

        // Khác shakeTray (gom cả mâm thì lấp chỗ bằng món bất kỳ): đổi món là
        // thay đúng một vị trí — hết món cùng vai trò thì trả rỗng để giao
        // diện báo thật, không lặng lẽ đưa món sai vai trò vào mâm.
        return collect();
    }

    /**
     * REC-005: ứng viên đổi món đã chấm điểm (điểm cao trước) cho danh sách
     * "Chọn món" trong popup đổi món.
     *
     * @param  array<string, mixed>  $input
     * @param  int[]  $excludeDishIds
     * @return Collection<int, Dish>
     */
    public function trayCandidates(array $input, string $role, array $excludeDishIds, int $limit = 40): Collection
    {
        $pickInput = $this->trayPickInput($input, $role);

        return $this->trayRolePool($input, $role, $excludeDishIds)
            ->map(fn (Dish $d) => ['dish' => $d, 'score' => $this->score($d, $pickInput, null)])
            ->sortByDesc('score')
            ->take($limit)
            ->map(fn ($row) => $row['dish'])
            ->values();
    }

    /**
     * REC-005: chọn NGẪU NHIÊN một món thay thế cho vị trí trong mâm (ngẫu
     * nhiên có trọng số như lắc chính; đổi shake_index để lấy kết quả khác).
     *
     * @param  array<string, mixed>  $input
     * @param  int[]  $excludeDishIds
     */
    public function traySwapRandom(array $input, string $role, array $excludeDishIds): ?Dish
    {
        $pool = $this->trayRolePool($input, $role, $excludeDishIds);
        if ($pool->isEmpty()) {
            return null;
        }
        $seed = hash('sha256', implode('|', [
            $input['user_id'] ?? 'guest',
            now()->toDateString(),
            $input['slot'],
            'tray-swap',
            $role,
            (string) ($input['shake_index'] ?? 0),
        ]));
        $dish = $this->pick($pool, $this->trayPickInput($input, $role), $excludeDishIds, $seed);
        RecommendationLog::create([
            'user_id' => $input['user_id'] ?? null,
            'slot' => $input['slot'],
            'seed' => $seed,
            'dish_id' => $dish->id,
            'relaxed_level' => 0,
            'context' => ['mode' => 'home', 'swap' => true, 'role' => $role, 'pool_size' => $pool->count()],
        ]);

        return $dish;
    }

    /** Input chấm điểm cho một vị trí mâm: calo mục tiêu chia theo tỷ trọng vai trò. */
    private function trayPickInput(array $input, string $role): array
    {
        if (! empty($input['target_kcal'])) {
            $input['target_kcal'] = $input['target_kcal'] * (self::TRAY_KCAL_SHARE[$role] ?? 0.25);
        }

        return $input;
    }

    /** Loại (loai:) của món, bỏ tiền tố — dùng cho phân vai mâm cơm. */
    private function dishLoai(Dish $dish): array
    {
        return $dish->tags
            ->pluck('slug')
            ->filter(fn ($s) => str_starts_with($s, 'loai:'))
            ->map(fn ($s) => substr($s, 5))
            ->all();
    }

    private function trayEligible(Dish $dish): bool
    {
        return array_intersect($this->dishLoai($dish), self::TRAY_EXCLUDED_LOAI) === [];
    }

    /**
     * REC-004: món "bình dân" — cách làm không quá BINH_DAN_MAX_STEPS bước và
     * MỌI nguyên liệu (trừ phần bỏ đi như xương/vỏ) đều phổ biến trong kho:
     * nguyên liệu càng xuất hiện ở nhiều món càng là đồ chợ nào cũng có, giá
     * bình dân; nguyên liệu chỉ 1–3 món dùng tới thường là đặc sản/đồ hiếm.
     */
    private function binhDanEligible(Dish $dish): bool
    {
        if (count($dish->recipe_steps ?? []) > self::BINH_DAN_MAX_STEPS) {
            return false;
        }
        $freq = $this->ingredientFrequency();
        foreach ($dish->dishIngredients as $line) {
            if ($line->is_discarded) {
                continue;
            }
            if (($freq[$line->ingredient_id] ?? 0) < self::BINH_DAN_MIN_INGREDIENT_FREQ) {
                return false;
            }
        }

        return true;
    }

    /** @return array<int, int> ingredient_id => số món trong kho dùng nguyên liệu đó */
    private function ingredientFrequency(): array
    {
        return $this->ingredientFreqCache ??= DB::table('dish_ingredients')
            ->selectRaw('ingredient_id, COUNT(DISTINCT dish_id) AS c')
            ->groupBy('ingredient_id')
            ->pluck('c', 'ingredient_id')
            ->map(fn ($c) => (int) $c)
            ->all();
    }

    private function dishRole(Dish $dish): string
    {
        $loai = $this->dishLoai($dish);
        if (in_array('canh', $loai, true)) {
            return 'canh';
        }
        $main = $dish->dishIngredients->sortByDesc('quantity')->first()?->ingredient;
        // "Món chính" của mâm phải là món đạm (thịt/thuỷ sản/trứng/đậu phụ…):
        // món gắn nhãn mon-chinh nhưng nguyên liệu chính toàn rau (rau xào, rau
        // luộc) là món RAU của mâm, không phải món chính.
        if (in_array('mon-chinh', $loai, true)
            && in_array($main?->category, ['thit', 'thuy-san', 'khac'], true)) {
            return 'chinh';
        }
        if (in_array('goi-nom', $loai, true) || in_array($main?->category, ['rau-cu', 'rau-gia-vi'], true)) {
            return 'rau';
        }

        return 'phu';
    }

    /** Nguyên liệu chính (định lượng lớn nhất) — căn cứ đa dạng trong mâm. */
    private function mainIngredientId(Dish $dish): ?int
    {
        return $dish->dishIngredients->sortByDesc('quantity')->first()?->ingredient_id;
    }

    private function roleMatches(string $dishRole, string $wanted): bool
    {
        // Suất "phu" ưu tiên món chính thứ hai, sau đó mới đến món phụ nói chung.
        return $wanted === 'phu'
            ? in_array($dishRole, ['phu', 'chinh'], true)
            : $dishRole === $wanted;
    }

    private function pool(array $input, int $level, array $allergens, bool $tray = false): Collection
    {
        // tags luôn nạp sẵn: nhãn diet suy từ thành phần (DishTagDeriver) là căn
        // cứ lọc chay chính xác hơn nhóm nguyên liệu (bắt được cả mắm/nước mắm).
        $with = ['names' => fn ($q) => $q->where('is_primary', true), 'region:id,name', 'nutrition', 'mealSlots', 'tags:id,slug'];
        if ($tray) {
            // Phân vai mâm cơm cần nguyên liệu chính của món.
            $with[] = 'dishIngredients.ingredient:id,category,slug';
        }
        $query = Dish::query()
            ->with($with)
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
        // Cờ ăn ngoài/tự nấu là lọc ƯU TIÊN theo chế độ lắc (REC-003): áp ở mức
        // 0–1 và nới từ mức 2 như lọc vùng — gần như mọi món trong kho đều nấu
        // được ở nhà và bán ở quán, giữ cứng cờ này chỉ gây cạn kho vô ích.
        // Lọc cứng tuyệt đối duy nhất vẫn là dị ứng.
        if ($level < 2 && ! empty($input['eat_out'])) {
            $query->where('is_eat_out', true);
        }
        if ($level < 2 && ! empty($input['home_cook'])) {
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
                // Nhãn diet:man đã suy từ MỌI thành phần (kể cả mắm tôm, nước mắm
                // thuộc nhóm gia vị) — món bị gắn nhãn này không bao giờ là chay.
                if ($dish->tags->contains('slug', 'diet:man')) {
                    return false;
                }
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
        $confFactor = match ($n?->confidence) {
            'high' => 1.0, 'medium' => 0.7, 'low' => 0.4, default => 0.2
        };
        $dataScore = 0.5 * $confFactor + 0.5 * ($dish->completeness_level >= 2 ? 1.0 : 0.5);

        $score = self::WEIGHTS['slot'] * $slotScore
            + self::WEIGHTS['kcal'] * $kcalScore
            + self::WEIGHTS['diversity'] * $diversity
            + self::WEIGHTS['data'] * $dataScore;

        return max($score, 0.01);
    }
}
