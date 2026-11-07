<?php

declare(strict_types=1);

namespace App\Http\Controllers\PublicWeb;

use App\Combinations\CombinationDisplay;
use App\Cultural\TcmDisplay;
use App\Http\Controllers\Controller;
use App\Models\Dish;
use App\Models\Ingredient;
use App\Models\Region;
use App\Models\UserProfile;
use App\Profile\TdeeCalculator;
use App\Recommendation\DrinkRecommender;
use App\Recommendation\Recommender;
use App\Taxonomy\AllergenDisplay;
use App\Taxonomy\TagFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** API công khai cho trang người dùng: danh mục món + nút Lắc (GĐ4). */
class CatalogController extends Controller
{
    public function regions(): JsonResponse
    {
        // DATA-010: vùng miền của món chỉ còn 3 miền chính (+ cả nước) —
        // tỉnh/thành cụ thể sống ở phân loại "Đặc sản" (facet dac-san).
        return response()->json(
            Region::query()->select(['id', 'name'])
                ->whereIn('level', ['country', 'macro_region'])
                ->orderBy('name')->get()
        );
    }

    public function index(Request $request): JsonResponse
    {
        $query = Dish::query()
            ->with(['names' => fn ($q) => $q->where('is_primary', true)
                // Mỗi món có tên chính theo từng locale; giao diện tiếng Việt
                // luôn lấy tên 'vi' làm names[0] (khớp với sort=name).
                ->orderByRaw("CASE WHEN locale = 'vi' THEN 0 ELSE 1 END"), 'region:id,name', 'nutrition', 'mealSlots', 'tags:id,slug,name', 'media']);
        if ($q = trim((string) $request->query('q', ''))) {
            $query->whereHas('names', fn ($w) => $w->where('name', 'like', "%{$q}%"));
        }
        if ($slot = $request->query('slot')) {
            $query->whereHas('mealSlots', fn ($w) => $w->where('slot', $slot));
        }
        if ($request->boolean('with_nutrition')) {
            $query->whereHas('nutrition');
        }
        // Lọc theo nhãn (TAXONOMY.md §7): khác facet = AND, trong facet = OR.
        foreach (TagFilter::slugsByFacet($request) as $slugs) {
            $query->whereHas('tags', fn ($w) => $w->whereIn('tags.slug', $slugs));
        }
        // Loại món chứa chất gây dị ứng: allergen_free=ca,dau-phong
        if ($request->filled('allergen_free')) {
            $slugs = collect(explode(',', (string) $request->query('allergen_free')))
                ->map(fn ($v) => 'allergen:'.trim($v))->filter(fn ($v) => $v !== 'allergen:')->all();
            if ($slugs !== []) {
                $query->whereDoesntHave('tags', fn ($w) => $w->whereIn('tags.slug', $slugs));
            }
        }
        // Trang Khám phá chuyên dụng (UI-015): lọc vùng + sắp xếp + cỡ trang.
        if ($regionId = $request->query('region_id')) {
            $query->where('dishes.region_id', (int) $regionId);
        }
        // Bộ lọc mở rộng theo dưỡng chất (UI-019): ngưỡng trên một khẩu phần,
        // nhiều lựa chọn áp cùng lúc theo AND. Món chưa có số không đạt lọc.
        // Chỉ dùng các cột dish_nutrition thật sự có (engine không tính natri
        // theo món — natri chỉ có ở snapshot nguyên liệu).
        $nutrientRules = [
            'protein-high' => ['protein_g', '>=', 20],
            'kcal-low' => ['kcal_max', '<=', 400],
            'fiber-high' => ['fiber_g', '>=', 5],
            'carb-low' => ['carb_g', '<=', 30],
            'fat-low' => ['fat_g', '<=', 10],
        ];
        $nutrients = collect(explode(',', (string) $request->query('nutrient', '')))
            ->map(fn ($v) => trim($v))->filter()->all();
        foreach ($nutrients as $code) {
            if (isset($nutrientRules[$code])) {
                [$col, $op, $val] = $nutrientRules[$code];
                $query->whereHas('nutrition', fn ($w) => $w->where($col, $op, $val));
            }
        }
        match ($request->query('sort')) {
            'name' => $query->leftJoin('dish_names', function ($j) {
                // is_primary là theo từng locale (vi/en) — phải khoá locale vi
                // kẻo mỗi món bị nhân đôi dòng và total phình lên.
                $j->on('dish_names.dish_id', '=', 'dishes.id')
                    ->where('dish_names.is_primary', true)
                    ->where('dish_names.locale', 'vi');
            })->orderBy('dish_names.name')->select('dishes.*'),
            'kcal_asc', 'kcal_desc' => $query->leftJoin('dish_nutrition', 'dish_nutrition.dish_id', '=', 'dishes.id')
                ->orderByRaw('dish_nutrition.kcal_min IS NULL')
                ->orderBy('dish_nutrition.kcal_min', $request->query('sort') === 'kcal_asc' ? 'asc' : 'desc')
                ->select('dishes.*'),
            // UI-019: sắp xếp theo độ uy tín của con số (confidence cao trước,
            // món chưa có số xuống cuối) và theo lượng đạm.
            'confidence' => $query->leftJoin('dish_nutrition', 'dish_nutrition.dish_id', '=', 'dishes.id')
                ->orderByRaw('dish_nutrition.confidence IS NULL')
                ->orderByRaw("FIELD(dish_nutrition.confidence, 'high', 'medium', 'low')")
                ->orderByDesc('dishes.id')
                ->select('dishes.*'),
            'protein_desc' => $query->leftJoin('dish_nutrition', 'dish_nutrition.dish_id', '=', 'dishes.id')
                ->orderByRaw('dish_nutrition.protein_g IS NULL')
                ->orderByDesc('dish_nutrition.protein_g')
                ->select('dishes.*'),
            default => $query->orderByDesc('dishes.id'),
        };
        $perPage = max(1, min(48, (int) $request->query('per_page', 24)));

        $page = $query->paginate($perPage);
        // IMG-001: ảnh chính của món (nếu có) để thẻ món hiển thị.
        $page->getCollection()->transform(function (Dish $d) {
            $d->setAttribute('image', $d->primaryImage());

            return $d;
        });

        return response()->json($page);
    }

    public function show(Dish $dish): JsonResponse
    {
        // Nhãn cách làm có dấu để giao diện không hiển thị mã slug thô (nau, luoc…).
        $dish->setAttribute('cooking_method_label', $dish->cookingMethodLabel());
        // TCM-000: nhãn mức phân biệt "Mức 2 (có note)" cho món nâng bù.
        $dish->setAttribute('level_label', $dish->levelLabel());
        $dish->load([
            'names', 'region', 'mealSlots', 'nutrition', 'tags:id,slug,name',
            'dishIngredients.ingredient:id,name_vi,slug',
            'parent.names' => fn ($q) => $q->where('is_primary', true),
            // IMG-001: ảnh món + ảnh của từng nguyên liệu trong món.
            'media', 'dishIngredients.ingredient.media',
            // TCM-000: tổng hợp tính vị (null khi dưới ngưỡng hiển thị).
            'tcmSummary',
        ]);
        $dish->setAttribute('image', $dish->primaryImage());
        foreach ($dish->dishIngredients as $line) {
            $line->ingredient->setAttribute('image', $line->ingredient->primaryImage());
        }
        $variants = Dish::where('parent_dish_id', $dish->id)
            ->with(['names' => fn ($q) => $q->where('is_primary', true)])
            ->get()
            ->map(fn (Dish $v) => ['slug' => $v->slug, 'name' => $v->displayName()]);

        return response()->json([
            'dish' => $dish,
            'variants' => $variants,
            // TCM-000/TCM-003: khối "Tính vị:" — mặc định hiển thị, nhưng
            // TcmDisplay trả null khi coverage <30% (không kết luận thay vì đoán).
            'tcm' => TcmDisplay::forDish($dish),
            // FOOD-COMBINE-001: cặp kết hợp cần lưu ý mà món chứa cả hai phía
            // (notes = tầng 1–2 hiện ở khối nguyên liệu; folk = tầng 3 thu
            // gọn). Chỉ là lớp thông tin — không vào gợi ý/lọc/chấm điểm.
            'combinations' => CombinationDisplay::forDish($dish),
            'combinations_disclaimer' => CombinationDisplay::DISCLAIMER,
            // QD-ALLERGEN (2026-11-02): nhãn dị ứng ở tầng HIỂN THỊ — QD-01
            // tách "Cá"/"Nước mắm", QD-02 đánh dấu "có thể có" cho cờ chỉ từ
            // dòng tuỳ chọn. Chỉ trình bày; lọc cứng dùng dish tags như cũ.
            'allergen_display' => AllergenDisplay::forDish($dish),
            // UI-003: khối minh bạch "Con số này từ đâu ra"
            'provenance' => $dish->nutrition === null ? null : [
                'fct_source' => $dish->nutrition->fct_source,
                'engine_version' => $dish->nutrition->engine_version,
                'computed_at' => $dish->nutrition->computed_at,
                'missing_ingredients' => $dish->nutrition->missing_ingredients,
                // UI-021: tên hiển thị của nguyên liệu chưa tra được (dữ liệu lưu
                // bằng slug — trước đây trang hiển thị thẳng slug cho người dùng).
                'missing_ingredient_names' => Ingredient::query()
                    ->whereIn('slug', $dish->nutrition->missing_ingredients ?? [])
                    ->pluck('name_vi')->values(),
                // UI-022: slug nguyên liệu chưa kiểm định — trang món gắn nhãn
                // ngay trên từng dòng của danh sách nguyên liệu bên dưới.
                'unverified_slugs' => $dish->nutrition->unverified_ingredients ?? [],
                // Cờ "chưa kiểm định": nguyên liệu dùng nguồn thứ cấp (hiển thị cảnh báo ở UI)
                'unverified_ingredients' => $dish->dishIngredients
                    ->filter(fn ($di) => in_array($di->ingredient->slug, $dish->nutrition->unverified_ingredients ?? [], true))
                    ->map(fn ($di) => $di->ingredient->name_vi)
                    ->values(),
                'contributing_ingredients' => $dish->dishIngredients
                    ->reject(fn ($di) => in_array($di->ingredient->slug, $dish->nutrition->missing_ingredients ?? [], true))
                    ->map(fn ($di) => $di->ingredient->name_vi)
                    ->values(),
            ],
        ]);
    }

    public function shake(Request $request, Recommender $recommender): JsonResponse
    {
        $data = $request->validate([
            'slot' => ['required', 'string', 'in:sang,trua,chieu,toi,an-vat'],
            'target_kcal' => ['nullable', 'numeric', 'min:50', 'max:3000'],
            'region_id' => ['nullable', 'integer', 'exists:regions,id'],
            'vegetarian' => ['nullable', 'boolean'],
            'eat_out' => ['nullable', 'boolean'],
            // REC-003: lắc hai chế độ — ăn ngoài (1 món) / mâm cơm tự nấu (1–4 món).
            'mode' => ['nullable', 'string', 'in:eat_out,home'],
            'dish_count' => ['nullable', 'integer', 'min:1', 'max:4'],
            // REC-004: tuỳ chọn "Bữa cơm bình dân" cho mâm cơm tự nấu.
            'binh_dan' => ['nullable', 'boolean'],
            'include_drink' => ['nullable', 'boolean'],
            'home_cook' => ['nullable', 'boolean'],
            'allergens' => ['nullable', 'array'],
            'allergens.*' => ['string', 'max:64'],
            'recent_dish_ids' => ['nullable', 'array'],
            'recent_dish_ids.*' => ['integer'],
            'shake_index' => ['nullable', 'integer', 'min:0'],
        ]);

        [$engineInput, $profile] = $this->shakeEngineInput($request, $data);

        // REC-003: quyết định chế độ lắc — tham số tường minh > hồ sơ > cờ cũ > mặc định ăn ngoài.
        $mode = $data['mode'] ?? null;
        if ($mode === null && ($profile ?? null) !== null && ! empty($profile->shake_mode)) {
            $mode = $profile->shake_mode;
        }
        if ($mode === null) {
            $mode = ! empty($data['home_cook']) ? 'home' : 'eat_out';
        }
        $dishCount = (int) ($data['dish_count'] ?? (($profile ?? null)?->tray_dish_count ?? 3));
        $dishCount = max(1, min(4, $dishCount));
        // REC-004: bình dân — tham số tường minh > hồ sơ > tắt.
        $binhDan = (bool) ($data['binh_dan'] ?? (($profile ?? null)?->tray_binh_dan ?? false));

        // Kho trống toàn cục = dữ liệu đang được dựng lại (seed), không phải do
        // bộ lọc của người dùng — phải báo đúng sự thật thay vì kêu nới bộ lọc.
        $catalogEmpty = Dish::whereHas('nutrition')->count() === 0;
        $updatingSuggestion = 'Hệ thống đang cập nhật lại dữ liệu món ăn — vui lòng thử lại sau ít phút.';

        if ($mode === 'home') {
            $result = $recommender->shakeTray([...$engineInput, 'dish_count' => $dishCount, 'binh_dan' => $binhDan]);
            $dishes = [];
            foreach ($result['dishes'] as $row) {
                $row['dish']->loadMissing('dishIngredients.ingredient:id,name_vi,slug');
                $dishes[] = $this->presentShakeDish($row['dish'], $row['role']);
            }
            $withNutrition = array_filter($dishes, fn ($d) => $d['nutrition'] !== null);

            return response()->json([
                'mode' => 'home',
                'dish_count' => $dishCount,
                'binh_dan' => $binhDan,
                'dishes' => $dishes,
                'dish' => $dishes[0] ?? null,
                'tray_kcal' => $withNutrition === [] ? null : [
                    'kcal_min' => array_sum(array_column(array_column($withNutrition, 'nutrition'), 'kcal_min')),
                    'kcal_max' => array_sum(array_column(array_column($withNutrition, 'nutrition'), 'kcal_max')),
                    'dishes_counted' => count($withNutrition),
                ],
                'seed' => $result['seed'],
                'relaxed_level' => $result['relaxed_level'],
                'pool_size' => $result['pool_size'],
                'suggestion' => $dishes === [] && $catalogEmpty ? $updatingSuggestion : ($result['suggestion'] ?? null),
                ...$this->drinkResult($data, $engineInput, 'home', $result['seed'], $dishes !== []),
            ]);
        }

        $result = $recommender->shake([...$engineInput, 'eat_out' => true, 'home_cook' => false]);
        $dish = $result['dish'];
        if ($dish !== null) {
            $dish->loadMissing('dishIngredients.ingredient:id,name_vi,slug');
        }
        $presented = $dish === null ? null : $this->presentShakeDish($dish);

        return response()->json([
            'mode' => 'eat_out',
            'dish_count' => 1,
            'dishes' => $presented === null ? [] : [$presented],
            'dish' => $presented,
            'seed' => $result['seed'],
            'relaxed_level' => $result['relaxed_level'],
            'pool_size' => $result['pool_size'],
            'suggestion' => $presented === null && $catalogEmpty ? $updatingSuggestion : ($result['suggestion'] ?? null),
            ...$this->drinkResult($data, $engineInput, 'eat_out', $result['seed'], $presented !== null),
        ]);
    }

    private function drinkResult(array $data, array $input, string $mode, string $seed, bool $hasDish): array
    {
        if (empty($data['include_drink'])) {
            return [];
        }
        $drink = $hasDish ? app(DrinkRecommender::class)->pick($input, $mode, $seed) : null;

        return [
            'drink' => $drink === null ? null : [
                'slug' => $drink->slug,
                'name' => $drink->name_vi,
                'kind' => $drink->kind,
                'alcohol' => false,
                'prep_minutes' => $drink->prep_minutes,
                'ingredients' => $drink->ingredients ?? [],
                'recipe_steps' => $drink->recipe_steps ?? [],
                'safety_flags' => $drink->safety_flags ?? [],
                'uncertain' => $drink->uncertain,
                'uncertain_note' => $drink->uncertain_note,
                'image' => $drink->primaryImage(),
            ],
            'drink_suggestion' => $drink !== null ? null : ($hasDish
                ? 'Chưa có nước uống phù hợp với bộ lọc hiện tại; loại chưa rõ thành phần được bỏ qua khi cần lọc dị ứng hoặc ăn chay.'
                : 'Chưa gợi ý nước uống vì chưa tìm được món ăn phù hợp.'),
        ];
    }

    /**
     * Ngữ cảnh cá nhân hoá dùng chung cho lắc và đổi món (USR-004 + REC-005):
     * gộp dị ứng từ hồ sơ, suy chế độ chay từ hồ sơ, lấy món gần đây từ nhật
     * ký thật và mục tiêu calo từ TDEE còn lại của ngày.
     *
     * @return array{0: array<string, mixed>, 1: ?UserProfile}
     */
    private function shakeEngineInput(Request $request, array $data): array
    {
        $allergens = $data['allergens'] ?? [];
        $vegetarian = $data['vegetarian'] ?? false;
        $recentIds = $data['recent_dish_ids'] ?? [];
        $targetKcal = $data['target_kcal'] ?? null;
        $user = $request->user();
        $profile = null;
        if ($user !== null) {
            $profile = $user->profile;
            if ($profile !== null) {
                $allergens = array_values(array_unique(array_merge(
                    $allergens,
                    $profile->excluded_ingredients ?? [],
                )));
                if (in_array($profile->diet_type, ['chay', 'chay-truong'], true)) {
                    $vegetarian = true;
                }
            }
            // Tầng 2 lấy từ meal_logs thật (mới nhất đứng cuối, đúng thứ tự engine kỳ vọng)
            $logIds = $user->mealLogs()->latest('eaten_at')->limit(10)->pluck('dish_id')->reverse()->values()->all();
            $recentIds = array_values(array_unique([...$logIds, ...$recentIds]));
            // Tầng 3 calo: mục tiêu = phần calo còn lại của ngày chia theo tỷ lệ bữa
            if ($targetKcal === null && $profile !== null) {
                $calc = app(TdeeCalculator::class);
                $tdee = $calc->for($profile);
                if ($tdee !== null) {
                    $eatenMid = $user->mealLogs()->whereDate('eaten_at', now()->toDateString())
                        ->with('dish.nutrition')->get()
                        ->sum(fn ($log) => $log->dish?->nutrition !== null
                            ? ($log->dish->nutrition->kcal_min + $log->dish->nutrition->kcal_max) / 2
                            : 0);
                    $tdeeMid = ($tdee['tdee_min'] + $tdee['tdee_max']) / 2;
                    $remaining = max($tdeeMid - $eatenMid, 150);
                    $ratios = TdeeCalculator::SLOT_RATIOS;
                    $order = ['sang', 'trua', 'chieu', 'toi'];
                    $idx = array_search($data['slot'], $order, true);
                    if ($idx !== false) {
                        $denom = array_sum(array_map(fn ($s) => $ratios[$s], array_slice($order, $idx)));
                        $targetKcal = $remaining * $ratios[$data['slot']] / max($denom, 0.01);
                    } elseif ($data['slot'] === 'an-vat') {
                        $targetKcal = $remaining * $ratios['an-vat'];
                    }
                }
            }
        }

        return [[
            ...$data,
            'user_id' => $user?->id,
            'vegetarian' => $vegetarian,
            'allergens' => $allergens,
            'recent_dish_ids' => $recentIds,
            'target_kcal' => $targetKcal,
            'shake_index' => $data['shake_index'] ?? 0,
        ], $profile];
    }

    /** REC-005: đổi NGẪU NHIÊN một món trong mâm cơm (nút Đổi → Ngẫu nhiên). */
    public function traySwapRandom(Request $request, Recommender $recommender): JsonResponse
    {
        $data = $request->validate($this->swapRules());
        [$engineInput] = $this->shakeEngineInput($request, $data);
        $dish = $recommender->traySwapRandom($engineInput, $data['role'], $data['exclude_dish_ids'] ?? []);
        if ($dish === null) {
            return response()->json([
                'dish' => null,
                'suggestion' => 'Không còn món khác phù hợp cho vị trí này với bộ lọc hiện tại.',
            ]);
        }
        $dish->loadMissing('dishIngredients.ingredient:id,name_vi,slug');

        return response()->json(['dish' => $this->presentShakeDish($dish, $data['role'])]);
    }

    /** REC-005: danh sách ứng viên cho "Chọn món" trong popup đổi món của mâm. */
    public function traySwapCandidates(Request $request, Recommender $recommender): JsonResponse
    {
        $data = $request->validate($this->swapRules());
        [$engineInput] = $this->shakeEngineInput($request, $data);
        $dishes = $recommender->trayCandidates($engineInput, $data['role'], $data['exclude_dish_ids'] ?? [])
            ->map(function (Dish $dish) use ($data) {
                $dish->loadMissing('dishIngredients.ingredient:id,name_vi,slug');

                return $this->presentShakeDish($dish, $data['role']);
            });

        return response()->json(['dishes' => $dishes]);
    }

    /** @return array<string, mixed> */
    private function swapRules(): array
    {
        return [
            'slot' => ['required', 'string', 'in:sang,trua,chieu,toi,an-vat'],
            'target_kcal' => ['nullable', 'numeric', 'min:50', 'max:3000'],
            'region_id' => ['nullable', 'integer', 'exists:regions,id'],
            'vegetarian' => ['nullable', 'boolean'],
            'binh_dan' => ['nullable', 'boolean'],
            'allergens' => ['nullable', 'array'],
            'allergens.*' => ['string', 'max:64'],
            'recent_dish_ids' => ['nullable', 'array'],
            'recent_dish_ids.*' => ['integer'],
            'shake_index' => ['nullable', 'integer', 'min:0'],
            'role' => ['required', 'string', 'in:chinh,canh,rau,phu'],
            'exclude_dish_ids' => ['nullable', 'array', 'max:10'],
            'exclude_dish_ids.*' => ['integer'],
        ];
    }

    /** Presenter dùng chung cho món lắc đơn và từng món trong mâm cơm (REC-003). */
    private function presentShakeDish(Dish $dish, ?string $role = null): array
    {
        return [
            'id' => $dish->id,
            'slug' => $dish->slug,
            'name' => $dish->displayName(),
            'region' => $dish->region?->name,
            'cooking_method' => $dish->cooking_method,
            'cooking_method_label' => $dish->cookingMethodLabel(),
            'serving_desc' => $dish->serving_desc,
            'slots' => $dish->mealSlots->pluck('slot'),
            'role' => $role,
            'role_label' => $role === null ? null : (Recommender::TRAY_ROLE_LABELS[$role] ?? $role),
            // IMG-001: ảnh chính của món lắc (nếu có).
            'image' => $dish->primaryImage(),
            // Hoàn thiện mục 5a: cảnh báo kết hợp trong kết quả Lắc — CHỈ hiển
            // thị. CombinationDisplay::forDish chỉ gắn cặp thực phẩm–thực phẩm;
            // cặp thuốc×thực phẩm không tự gắn vào món (nguyên tắc cũ giữ nguyên).
            // Không chặn, không lọc, không chấm điểm — Recommender không đổi.
            'combinations' => CombinationDisplay::forDish($dish),
            'combinations_disclaimer' => CombinationDisplay::DISCLAIMER,
            'nutrition' => $dish->nutrition === null ? null : [
                'kcal_min' => $dish->nutrition->kcal_min,
                'kcal_max' => $dish->nutrition->kcal_max,
                'protein_g' => $dish->nutrition->protein_g,
                'fat_g' => $dish->nutrition->fat_g,
                'carb_g' => $dish->nutrition->carb_g,
                'confidence' => $dish->nutrition->confidence,
                'unverified_ingredients' => $dish->dishIngredients
                    ->filter(fn ($di) => in_array($di->ingredient->slug, $dish->nutrition->unverified_ingredients ?? [], true))
                    ->map(fn ($di) => $di->ingredient->name_vi)
                    ->values(),
            ],
        ];
    }
}
