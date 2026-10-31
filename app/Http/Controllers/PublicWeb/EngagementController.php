<?php

declare(strict_types=1);

namespace App\Http\Controllers\PublicWeb;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\FruitLog;
use App\Models\MealLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Yêu thích + ghi ăn hoa quả + thống kê ăn uống (UI-014). */
class EngagementController extends Controller
{
    public function ids(Request $request): JsonResponse
    {
        $favs = $request->user()->favorites()->get(['dish_id', 'fruit_id']);

        return response()->json([
            'dish_ids' => $favs->pluck('dish_id')->filter()->values(),
            'fruit_ids' => $favs->pluck('fruit_id')->filter()->values(),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $favs = $request->user()->favorites()
            ->with([
                'dish' => fn ($q) => $q->with([
                    'names' => fn ($n) => $n->where('is_primary', true),
                    'nutrition', 'region',
                ]),
                'fruit' => fn ($q) => $q->with('ingredient:id,slug,name_vi'),
            ])
            ->latest()
            ->get();

        return response()->json([
            'dishes' => $favs->filter(fn ($f) => $f->dish !== null)->map(fn ($f) => [
                'id' => $f->dish->id,
                'slug' => $f->dish->slug,
                'name' => $f->dish->names->first()?->name,
                'region' => $f->dish->region?->name,
                'nutrition' => $f->dish->nutrition === null ? null : [
                    'kcal_min' => $f->dish->nutrition->kcal_min,
                    'kcal_max' => $f->dish->nutrition->kcal_max,
                    'confidence' => $f->dish->nutrition->confidence,
                ],
            ])->values(),
            'fruits' => $favs->filter(fn ($f) => $f->fruit !== null)->map(fn ($f) => [
                'id' => $f->fruit->id,
                'slug' => $f->fruit->slug,
                'name_vi' => $f->fruit->ingredient?->name_vi,
                'kcal_per_100g' => $f->fruit->ingredient?->nutrition_snapshot['kcal_per_100g'] ?? null,
                'availability_months' => $f->fruit->availability_months ?? [],
                'main_season_months' => $f->fruit->main_season_months ?? [],
            ])->values(),
        ]);
    }

    public function toggle(Request $request): JsonResponse
    {
        $data = $request->validate([
            'dish_id' => ['nullable', 'integer', 'exists:dishes,id', 'required_without:fruit_id', 'prohibits:fruit_id'],
            'fruit_id' => ['nullable', 'integer', 'exists:fruits,id'],
        ]);
        $key = isset($data['dish_id']) ? 'dish_id' : 'fruit_id';
        $existing = $request->user()->favorites()->where($key, $data[$key])->first();
        if ($existing !== null) {
            $existing->delete();

            return response()->json(['favored' => false]);
        }
        $request->user()->favorites()->create([$key => $data[$key]]);

        return response()->json(['favored' => true], 201);
    }

    public function storeFruitLog(Request $request): JsonResponse
    {
        $data = $request->validate([
            'fruit_id' => ['required', 'integer', 'exists:fruits,id'],
            'eaten_at' => ['nullable', 'date'],
        ]);
        $log = $request->user()->fruitLogs()->create([
            'fruit_id' => $data['fruit_id'],
            'eaten_at' => $data['eaten_at'] ?? now(),
            'source' => 'manual',
        ]);

        return response()->json($log, 201);
    }

    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        $topDishes = MealLog::where('user_id', $user->id)
            ->selectRaw('dish_id, COUNT(*) as cnt')
            ->groupBy('dish_id')
            ->orderByDesc('cnt')
            ->limit(5)
            ->with(['dish' => fn ($q) => $q->with(['names' => fn ($n) => $n->where('is_primary', true)])])
            ->get()
            ->map(fn ($r) => [
                'name' => $r->dish?->names->first()?->name,
                'slug' => $r->dish?->slug,
                'count' => (int) $r->cnt,
            ]);
        $topFruits = FruitLog::where('user_id', $user->id)
            ->selectRaw('fruit_id, COUNT(*) as cnt')
            ->groupBy('fruit_id')
            ->orderByDesc('cnt')
            ->limit(5)
            ->with('fruit.ingredient:id,name_vi')
            ->get()
            ->map(fn ($r) => [
                'name' => $r->fruit?->ingredient?->name_vi,
                'slug' => $r->fruit?->slug,
                'count' => (int) $r->cnt,
            ]);

        return response()->json([
            'meals_total' => $user->mealLogs()->count(),
            'dishes_distinct' => $user->mealLogs()->distinct()->count('dish_id'),
            'top_dishes' => $topDishes,
            'fruits_total' => $user->fruitLogs()->count(),
            'fruits_distinct' => $user->fruitLogs()->distinct()->count('fruit_id'),
            'top_fruits' => $topFruits,
        ]);
    }
}
