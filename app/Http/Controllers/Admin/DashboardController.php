<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\Dish;
use App\Models\DishIngredient;
use App\Models\Ingredient;
use App\Models\ReferenceFood;
use Illuminate\Http\JsonResponse;

/** Số liệu tổng quan cho trang chủ quản trị. */
class DashboardController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'ingredients_total' => Ingredient::count(),
            'ingredients_pending_review' => Ingredient::where('needs_review', true)->count(),
            'dishes_total' => Dish::count(),
            'dishes_by_completeness' => Dish::query()
                ->selectRaw('completeness_level, COUNT(*) AS n')
                ->groupBy('completeness_level')
                ->pluck('n', 'completeness_level'),
            'dishes_with_ingredients' => DishIngredient::distinct('dish_id')->count('dish_id'),
            'dish_ingredient_rows' => DishIngredient::count(),
            'reference_foods_total' => ReferenceFood::count(),
            'recent_activity' => AdminAuditLog::with('user:id,name,email')
                ->latest()
                ->limit(10)
                ->get(),
        ]);
    }
}
