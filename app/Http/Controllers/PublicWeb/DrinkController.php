<?php

declare(strict_types=1);

namespace App\Http\Controllers\PublicWeb;

use App\Combinations\CombinationDisplay;
use App\Http\Controllers\Controller;
use App\Models\Drink;
use App\Models\Ingredient;
use App\Nutrition\DrinkNutritionService;
use App\Taxonomy\AllergenDisplay;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API công khai cho thư viện nước uống (DRINK-MODULE, catalogue v2):
 * danh sách lọc theo nhóm, kind (pha chế/đóng gói), nóng/lạnh, có cồn,
 * mức caffeine, độ ngọt (từ sugar_added_g hoặc khoảng đường đóng gói) và
 * tìm kiếm theo tên. Chi tiết phân biệt hai kind; dinh dưỡng pha chế tính
 * bằng DrinkNutritionService (dùng lại NutritionCalculator) từ các dòng đã
 * gắn `ingredient_slug` — kcal rượu đã bao gồm ethanol vì cộng từ kcal tham
 * chiếu, không cần logic ethanol riêng. Đồ uống còn dòng missing (đặc biệt
 * rượu mùi/siro chưa đo density) giữ kcal_status = 'chua-tinh-duoc' kèm danh
 * sách thiếu, không hiện số thiếu.
 */
class DrinkController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Drink::query()->orderBy('slug');

        if ($q = trim((string) $request->query('q', ''))) {
            $query->where('name_vi', 'like', "%{$q}%");
        }
        if ($group = $request->query('group')) {
            $query->where('drink_group', $group);
        }
        if ($kind = $request->query('kind')) {
            $query->where('kind', $kind);
        }
        if ($temperature = $request->query('temperature')) {
            $query->where('temperature', $temperature);
        }
        if ($request->query('alcohol') === '1') {
            $query->where('alcohol', true);
        }
        if ($request->query('alcohol') === '0') {
            $query->where('alcohol', false);
        }
        if ($caffeine = $request->query('caffeine_level')) {
            $query->where('caffeine_level', $caffeine);
        }
        // Độ ngọt: pha chế dùng sugar_added_g; đóng gói dùng min của khoảng.
        if ($sweet = $request->query('sweetness')) {
            $query->where(function ($w) use ($sweet) {
                $w->where(function ($p) use ($sweet) {
                    $p->where('kind', 'pha-che');
                    match ($sweet) {
                        'khong' => $p->where('sugar_added_g', '<=', 5),
                        'vua' => $p->whereBetween('sugar_added_g', [6, 25]),
                        'cao' => $p->where('sugar_added_g', '>', 25),
                        default => null,
                    };
                })->orWhere(function ($p) use ($sweet) {
                    $p->where('kind', 'dong-goi');
                    $min = "CAST(JSON_EXTRACT(sugar_range_g, '$.min') AS DECIMAL(10,2))";
                    match ($sweet) {
                        'khong' => $p->whereRaw("{$min} <= 5"),
                        'vua' => $p->whereRaw("{$min} BETWEEN 6 AND 25"),
                        'cao' => $p->whereRaw("{$min} > 25"),
                        default => null,
                    };
                });
            });
        }

        $perPage = min(120, max(1, (int) $request->query('per_page', 100)));
        $drinks = $query->with('media')->paginate($perPage);
        $drinks->getCollection()->transform(fn (Drink $d) => $this->summary($d));

        return response()->json($drinks);
    }

    public function show(Drink $drink): JsonResponse
    {
        $nutrition = app(DrinkNutritionService::class)->compute($drink);
        // Ảnh thumbnail cho hàng nguyên liệu (như trang món): chỉ các dòng
        // đã gắn ingredient_slug mới có ảnh từ hồ sơ nguyên liệu đó; dòng
        // chưa gắn thì image = null và giao diện dùng placeholder.
        $rawIngredients = $drink->ingredients ?? [];
        $slugs = collect($rawIngredients)
            ->map(fn ($line) => $line['ingredient_slug'] ?? null)
            ->filter()
            ->unique()
            ->values();
        $ingredientsBySlug = Ingredient::whereIn('slug', $slugs)
            ->with('media')
            ->get()
            ->keyBy('slug');
        $ingredients = collect($rawIngredients)
            ->map(fn ($line) => $line + [
                'image' => isset($line['ingredient_slug']) && $ingredientsBySlug->has($line['ingredient_slug'])
                    ? $ingredientsBySlug[$line['ingredient_slug']]->primaryImage()
                    : null,
            ])
            ->all();
        $counterpart = null;
        if ($drink->mocktail_counterpart_slug) {
            $counterpart = Drink::where('slug', $drink->mocktail_counterpart_slug)->first();
        }

        return response()->json([
            'drink' => [
                ...$this->summary($drink),
                'glass' => $drink->glass,
                'technique' => $drink->technique,
                'ice' => $drink->ice,
                'garnish' => $drink->garnish ?? [],
                'ingredients' => $ingredients,
                'recipe_steps' => $drink->recipe_steps ?? [],
                'sugar_note' => $drink->sugar_note,
                'alcohol_note' => $drink->alcohol_note,
                'safety_flags' => $drink->safety_flags ?? [],
                'difficulty' => $drink->difficulty,
                'prep_minutes' => $drink->prep_minutes,
                'best_occasion' => $drink->best_occasion,
                'food_pairing' => $drink->food_pairing ?? [],
                'packaging' => $drink->packaging,
                'sugar_range_g' => $drink->sugar_range_g,
                'alcohol_pct_range' => $drink->alcohol_pct_range,
                'label_note' => $drink->label_note,
                'brand_examples' => $drink->brand_examples ?? [],
                'cultural_note' => $drink->cultural_note,
                'recipe_sources' => $drink->recipe_sources ?? [],
                'uncertain' => $drink->uncertain,
                'uncertain_note' => $drink->uncertain_note,
                'source_note' => $drink->source_note,
                'needs_review' => $drink->needs_review,
                'nutrition' => $nutrition,
                'kcal_status' => $nutrition['status'] === 'computed' ? 'tinh-duoc' : 'chua-tinh-duoc',
                'kcal_note' => $nutrition['status'] === 'computed'
                    ? 'Tính từ công thức đã gắn hồ sơ nguyên liệu bằng lại engine món ăn; kcal của rượu (nếu có) đã bao gồm năng lượng ethanol vì cộng từ kcal tham chiếu.'
                    : ('Chưa tính được kcal: '.($nutrition['missing'] !== []
                        ? 'thiếu: '.implode('; ', array_slice($nutrition['missing'], 0, 5)).(count($nutrition['missing']) > 5 ? '…' : '')
                        : 'chưa đủ dữ liệu để tính.')),
                'mocktail_counterpart' => $counterpart ? $this->summary($counterpart) : null,
            ],
            // Dị ứng: suy từ các dòng nguyên liệu đã gắn hồ sơ kèm độ phủ —
            // xem AllergenDisplay::forDrink (không đọc "không suy được" thành
            // "không có dị ứng" khi độ phủ chưa đủ).
            'allergen' => AllergenDisplay::forDrink($drink),
            // Kết hợp cần lưu ý: chỉ khi có cặp đã thẩm định chạm tới đồ
            // uống này qua slug đồ uống (không tự kế thừa slug nguyên liệu).
            'combinations' => CombinationDisplay::forItem($drink->slug),
            'combinations_disclaimer' => CombinationDisplay::DISCLAIMER,
        ]);
    }

    /** @return array<string, mixed> */
    private function summary(Drink $drink): array
    {
        return [
            'id' => $drink->id,
            'slug' => $drink->slug,
            'name_vi' => $drink->name_vi,
            'drink_group' => $drink->drink_group,
            'kind' => $drink->kind,
            'temperature' => $drink->temperature,
            'serving_ml' => $drink->serving_ml,
            'liquid_ml' => $drink->liquid_ml,
            'sugar_added_g' => $drink->sugar_added_g,
            'sugar_range_g' => $drink->sugar_range_g,
            'caffeine_level' => $drink->caffeine_level,
            'caffeine_mg_min' => $drink->caffeine_mg_min,
            'caffeine_mg_max' => $drink->caffeine_mg_max,
            'caffeine_mg_note' => $drink->caffeine_mg_note,
            'key_indicators' => $drink->key_indicators ?? [],
            'alcohol' => $drink->alcohol,
            'alcohol_pct_estimate' => $drink->alcohol_pct_estimate,
            'alcohol_pct_range' => $drink->alcohol_pct_range,
            'packaging' => $drink->packaging,
            'uncertain' => $drink->uncertain,
            'needs_review' => $drink->needs_review,
            'image' => $drink->primaryImage(),
        ];
    }
}
