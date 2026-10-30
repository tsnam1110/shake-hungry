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
        return response()->json(
            \App\Models\Region::query()->select(['id', 'name'])->orderBy('name')->get()
        );
    }

    public function index(Request $request): JsonResponse
    {
        $query = Dish::query()
            ->with(['names' => fn ($q) => $q->where('is_primary', true), 'region:id,name', 'nutrition', 'mealSlots']);
        if ($q = trim((string) $request->query('q', ''))) {
            $query->whereHas('names', fn ($w) => $w->where('name', 'like', "%{$q}%"));
        }
        if ($slot = $request->query('slot')) {
            $query->whereHas('mealSlots', fn ($w) => $w->where('slot', $slot));
        }
        if ($request->boolean('with_nutrition')) {
            $query->whereHas('nutrition');
        }
        $query->orderByDesc('id');

        return response()->json($query->paginate(24));
    }

    public function show(Dish $dish): JsonResponse
    {
        return response()->json($dish->load([
            'names', 'region', 'mealSlots', 'nutrition',
            'dishIngredients.ingredient:id,name_vi,slug',
        ]));
    }

    public function shake(Request $request, Recommender $recommender): JsonResponse
    {
        $data = $request->validate([
            'slot' => ['required', 'string', 'in:sang,trua,chieu,toi,an-vat'],
            'target_kcal' => ['nullable', 'numeric', 'min:50', 'max:3000'],
            'region_id' => ['nullable', 'integer', 'exists:regions,id'],
            'vegetarian' => ['nullable', 'boolean'],
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

        $result = $recommender->shake([
            ...$data,
            'user_id' => $user?->id,
            'vegetarian' => $vegetarian,
            'allergens' => $allergens,
            'recent_dish_ids' => $recentIds,
            'target_kcal' => $targetKcal,
            'shake_index' => $data['shake_index'] ?? 0,
        ]);

        $dish = $result['dish'];

        return response()->json([
            'dish' => $dish === null ? null : [
                'id' => $dish->id,
                'slug' => $dish->slug,
                'name' => $dish->names->first()?->name,
                'region' => $dish->region?->name,
                'cooking_method' => $dish->cooking_method,
                'serving_desc' => $dish->serving_desc,
                'slots' => $dish->mealSlots->pluck('slot'),
                'nutrition' => $dish->nutrition === null ? null : [
                    'kcal_min' => $dish->nutrition->kcal_min,
                    'kcal_max' => $dish->nutrition->kcal_max,
                    'protein_g' => $dish->nutrition->protein_g,
                    'fat_g' => $dish->nutrition->fat_g,
                    'carb_g' => $dish->nutrition->carb_g,
                    'confidence' => $dish->nutrition->confidence,
                ],
            ],
            'seed' => $result['seed'],
            'relaxed_level' => $result['relaxed_level'],
            'pool_size' => $result['pool_size'],
            'suggestion' => $result['suggestion'] ?? null,
        ]);
    }
}
