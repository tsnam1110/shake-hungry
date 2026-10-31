<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\Contribution;
use App\Models\Dish;
use App\Models\DishIngredient;
use App\Models\DishName;
use App\Models\DishNutrition;
use App\Models\Fruit;
use App\Models\FruitPrice;
use App\Models\Ingredient;
use App\Models\IngredientAlias;
use App\Models\ReferenceFood;
use App\Models\User;
use App\Nutrition\SourceTier;
use Illuminate\Http\JsonResponse;

/**
 * Số liệu tổng quan cho trang chủ quản trị (ADM-008): số lượng bản ghi theo
 * từng loại dữ liệu + các chỉ số chất lượng (thiếu số, chưa kiểm định, chờ
 * duyệt) để biên tập viên biết phải xử lý gì tiếp theo. Trang danh sách
 * cũng dùng chính payload này cho thanh thống kê của mình.
 */
class DashboardController extends Controller
{
    public function __invoke(): JsonResponse
    {
        // Nguyên liệu: thiếu số / chưa kiểm định đọc từ snapshot (vài trăm dòng, quét PHP cho rõ).
        $missingNutrition = 0;
        $unverifiedIngredients = 0;
        foreach (Ingredient::pluck('nutrition_snapshot') as $snapshot) {
            if (($snapshot['kcal_per_100g'] ?? null) === null) {
                $missingNutrition++;
            }
            if (SourceTier::isSecondary($snapshot['fct_source'] ?? null)) {
                $unverifiedIngredients++;
            }
        }

        $dishesTotal = Dish::count();
        $dishesComputable = DishNutrition::count();

        return response()->json([
            'ingredients' => [
                'total' => Ingredient::count(),
                'pending_review' => Ingredient::where('needs_review', true)->count(),
                'by_category' => Ingredient::query()
                    ->selectRaw('category, COUNT(*) AS n')->groupBy('category')->pluck('n', 'category'),
                'missing_nutrition' => $missingNutrition,
                'unverified' => $unverifiedIngredients,
                'aliases' => IngredientAlias::count(),
            ],
            'dishes' => [
                'total' => $dishesTotal,
                'pending_review' => Dish::where('needs_review', true)->count(),
                'computable' => $dishesComputable,
                'missing_nutrition' => $dishesTotal - $dishesComputable,
                'unverified' => DishNutrition::whereRaw('JSON_LENGTH(unverified_ingredients) > 0')->count(),
                'by_completeness' => Dish::query()
                    ->selectRaw('completeness_level, COUNT(*) AS n')
                    ->groupBy('completeness_level')->pluck('n', 'completeness_level'),
                'by_diet' => [
                    'chay' => Dish::whereHas('tags', fn ($q) => $q->where('slug', 'diet:chay'))->count(),
                    'chay-trung-sua' => Dish::whereHas('tags', fn ($q) => $q->where('slug', 'diet:chay-trung-sua'))->count(),
                    'man' => Dish::whereHas('tags', fn ($q) => $q->where('slug', 'diet:man'))->count(),
                ],
                'with_ingredients' => DishIngredient::distinct('dish_id')->count('dish_id'),
                'ingredient_rows' => DishIngredient::count(),
                'with_aliases' => DishName::where('is_alias', true)->distinct('dish_id')->count('dish_id'),
            ],
            'fruits' => [
                'total' => Fruit::count(),
                'pending_review' => Fruit::where('needs_review', true)->count(),
                'with_prices' => FruitPrice::distinct('fruit_id')->count('fruit_id'),
                'price_rows' => FruitPrice::count(),
                'by_group' => Fruit::query()
                    ->selectRaw('classification_group, COUNT(*) AS n')
                    ->groupBy('classification_group')->pluck('n', 'classification_group'),
                'by_origin' => Fruit::query()
                    ->selectRaw('origin, COUNT(*) AS n')->groupBy('origin')->pluck('n', 'origin'),
            ],
            'contributions' => [
                'total' => Contribution::count(),
                'pending' => Contribution::where('status', 'pending')->count(),
                'approved' => Contribution::where('status', 'approved')->count(),
                'rejected' => Contribution::where('status', 'rejected')->count(),
            ],
            'users' => [
                'total' => User::count(),
                'admins' => User::where('role', 'admin')->count(),
            ],
            'reference_foods_total' => ReferenceFood::count(),
            'recent_activity' => AdminAuditLog::with('user:id,name,email')
                ->latest()
                ->limit(10)
                ->get(),
        ]);
    }
}
