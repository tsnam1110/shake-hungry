<?php

declare(strict_types=1);

namespace App\Http\Controllers\PublicWeb;

use App\Http\Controllers\Controller;
use App\Models\FruitLog;
use App\Models\MealLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Yêu thích + ghi ăn hoa quả + thống kê ăn uống (UI-014). */
class EngagementController extends Controller
{
    public function ids(Request $request): JsonResponse
    {
        $favs = $request->user()->favorites()->get(['dish_id', 'fruit_id', 'drink_id']);

        return response()->json([
            'dish_ids' => $favs->pluck('dish_id')->filter()->values(),
            'fruit_ids' => $favs->pluck('fruit_id')->filter()->values(),
            'drink_ids' => $favs->pluck('drink_id')->filter()->values(),
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
                'drink',
            ])
            ->latest()
            ->get();

        return response()->json([
            'dishes' => $favs->filter(fn ($f) => $f->dish !== null)->map(fn ($f) => [
                'id' => $f->dish->id,
                'slug' => $f->dish->slug,
                'name' => $f->dish->displayName(),
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
            'drinks' => $favs->filter(fn ($f) => $f->drink !== null)->map(fn ($f) => [
                'id' => $f->drink->id,
                'slug' => $f->drink->slug,
                'name_vi' => $f->drink->name_vi,
                'drink_group' => $f->drink->drink_group,
                'kind' => $f->drink->kind,
                'temperature' => $f->drink->temperature,
                'alcohol' => $f->drink->alcohol,
            ])->values(),
        ]);
    }

    public function toggle(Request $request): JsonResponse
    {
        $data = $request->validate([
            'dish_id' => ['nullable', 'integer', 'exists:dishes,id', 'required_without_all:fruit_id,drink_id', 'prohibits:fruit_id,drink_id'],
            'fruit_id' => ['nullable', 'integer', 'exists:fruits,id', 'prohibits:drink_id'],
            'drink_id' => ['nullable', 'integer', 'exists:drinks,id'],
        ]);
        $key = isset($data['dish_id']) ? 'dish_id' : (isset($data['fruit_id']) ? 'fruit_id' : 'drink_id');
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

    /** Quả đã ăn trong một ngày (trang Nhật ký UI-015). */
    public function fruitLogs(Request $request): JsonResponse
    {
        $date = $request->query('date', now()->toDateString());
        $logs = $request->user()->fruitLogs()
            ->with('fruit.ingredient:id,name_vi')
            ->whereDate('eaten_at', $date)
            ->orderBy('eaten_at')
            ->get()
            ->map(fn (FruitLog $l) => [
                'id' => $l->id,
                'fruit_id' => $l->fruit_id,
                'slug' => $l->fruit?->slug,
                'name' => $l->fruit?->ingredient?->name_vi,
                'eaten_at' => $l->eaten_at?->toIso8601String(),
            ]);

        return response()->json(['date' => $date, 'logs' => $logs]);
    }

    public function destroyFruitLog(Request $request, FruitLog $fruitLog): JsonResponse
    {
        abort_unless($fruitLog->user_id === $request->user()->id, 404);
        $fruitLog->delete();

        return response()->json(['ok' => true]);
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
                'name' => $r->dish?->displayName(),
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
