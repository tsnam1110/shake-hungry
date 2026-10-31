<?php

declare(strict_types=1);

namespace App\Http\Controllers\PublicWeb;

use App\Http\Controllers\Controller;
use App\Models\Dish;
use App\Recommendation\Recommender;
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
            \App\Models\Region::query()->select(['id', 'name'])
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
        foreach (\App\Taxonomy\TagFilter::slugsByFacet($request) as $slugs) {
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
        $dish->load([
            'names', 'region', 'mealSlots', 'nutrition', 'tags:id,slug,name',
            'dishIngredients.ingredient:id,name_vi,slug',
            'parent.names' => fn ($q) => $q->where('is_primary', true),
            // IMG-001: ảnh món + ảnh của từng nguyên liệu trong món.
            'media', 'dishIngredients.ingredient.media',
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
            // UI-003: khối minh bạch "Con số này từ đâu ra"
            'provenance' => $dish->nutrition === null ? null : [
                'fct_source' => $dish->nutrition->fct_source,
                'engine_version' => $dish->nutrition->engine_version,
                'computed_at' => $dish->nutrition->computed_at,
                'missing_ingredients' => $dish->nutrition->missing_ingredients,
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
            'home_cook' => ['nullable', 'boolean'],
            'allergens' => ['nullable', 'array'],
            'allergens.*' => ['string', 'max:64'],
            'recent_dish_ids' => ['nullable', 'array'],
            'recent_dish_ids.*' => ['integer'],
            'shake_index' => ['nullable', 'integer', 'min:0'],
        ]);

        // USR-004: người đã đăng nhập được cá nhân hoá từ hồ sơ + nhật ký thật.
        $allergens = $data['allergens'] ?? [];
        $vegetarian = $data['vegetarian'] ?? false;
        $recentIds = $data['recent_dish_ids'] ?? [];
        $targetKcal = $data['target_kcal'] ?? null;
        $user = $request->user();
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
                $calc = app(\App\Profile\TdeeCalculator::class);
                $tdee = $calc->for($profile);
                if ($tdee !== null) {
                    $eatenMid = $user->mealLogs()->whereDate('eaten_at', now()->toDateString())
                        ->with('dish.nutrition')->get()
                        ->sum(fn ($log) => $log->dish?->nutrition !== null
                            ? ($log->dish->nutrition->kcal_min + $log->dish->nutrition->kcal_max) / 2
                            : 0);
                    $tdeeMid = ($tdee['tdee_min'] + $tdee['tdee_max']) / 2;
                    $remaining = max($tdeeMid - $eatenMid, 150);
                    $ratios = \App\Profile\TdeeCalculator::SLOT_RATIOS;
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
        $catalogEmpty = \App\Models\Dish::whereHas('nutrition')->count() === 0;
        $updatingSuggestion = 'Hệ thống đang cập nhật lại dữ liệu món ăn — vui lòng thử lại sau ít phút.';

        $engineInput = [
            ...$data,
            'user_id' => $user?->id,
            'vegetarian' => $vegetarian,
            'allergens' => $allergens,
            'recent_dish_ids' => $recentIds,
            'target_kcal' => $targetKcal,
            'shake_index' => $data['shake_index'] ?? 0,
        ];

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
        ]);
    }

    /** Presenter dùng chung cho món lắc đơn và từng món trong mâm cơm (REC-003). */
    private function presentShakeDish(\App\Models\Dish $dish, ?string $role = null): array
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
            'role_label' => $role === null ? null : (\App\Recommendation\Recommender::TRAY_ROLE_LABELS[$role] ?? $role),
            // IMG-001: ảnh chính của món lắc (nếu có).
            'image' => $dish->primaryImage(),
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
