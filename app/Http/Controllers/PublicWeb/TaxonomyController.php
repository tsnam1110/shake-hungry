<?php

declare(strict_types=1);

namespace App\Http\Controllers\PublicWeb;

use App\Combinations\CombinationDisplay;
use App\Cultural\TcmDisplay;
use App\Http\Controllers\Controller;
use App\Models\Dish;
use App\Models\Fruit;
use App\Models\Ingredient;
use App\Models\Tag;
use App\Nutrition\SourceTier;
use App\Taxonomy\TagFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** API công khai cho hệ phân loại/nhãn (TAXONOMY.md §7): từ vựng + lọc nguyên liệu. */
class TaxonomyController extends Controller
{
    /** Toàn bộ nhãn trong đăng ký kèm số món / số nguyên liệu — nguồn cho chip lọc. */
    public function tags(): JsonResponse
    {
        $registry = config('taxonomy.tags', []);
        $counts = Tag::withCount(['dishes', 'ingredients'])->get()->keyBy('slug');

        $out = [];
        foreach ($registry as $slug => $meta) {
            $out[] = [
                'slug' => $slug,
                'name' => $meta['name'],
                'facet' => $meta['facet'],
                'dishes' => $counts->get($slug)?->dishes_count ?? 0,
                'ingredients' => $counts->get($slug)?->ingredients_count ?? 0,
            ];
        }

        return response()->json($out);
    }

    /** Danh mục nguyên liệu công khai: lọc theo category, nhãn, từ khoá. */
    public function ingredients(Request $request): JsonResponse
    {
        $query = Ingredient::query()->with(['tags:id,slug,name', 'aliases:id,ingredient_id,name,note', 'media'])->orderBy('name_vi');
        if ($q = trim((string) $request->query('q', ''))) {
            // Tìm cả tên gọi khác / tên vùng miền (ALIAS-001).
            $query->where(fn ($w) => $w->where('name_vi', 'like', "%{$q}%")
                ->orWhereHas('aliases', fn ($a) => $a->where('name', 'like', "%{$q}%")));
        }
        if ($category = $request->query('category')) {
            $query->where('category', $category);
        }
        foreach (TagFilter::slugsByFacet($request) as $slugs) {
            $query->whereHas('tags', fn ($w) => $w->whereIn('tags.slug', $slugs));
        }

        // Bộ lọc dưỡng chất (UI-019): ngưỡng trên 100g trong nutrition_snapshot.
        // Lọc "thấp" phải loại cả giá trị null (chưa biết không đồng nghĩa thấp).
        $nutrientRules = [
            'protein-high' => ['protein_g', '>=', 10, false],
            'kcal-low' => ['kcal_per_100g', '<=', 100, true],
            'fiber-high' => ['fiber_g', '>=', 3, false],
            'sodium-low' => ['sodium_mg', '<=', 120, true],
            'fat-low' => ['fat_g', '<=', 3, true],
        ];
        $nutrients = collect(explode(',', (string) $request->query('nutrient', '')))
            ->map(fn ($v) => trim($v))->filter()->all();
        foreach ($nutrients as $code) {
            if (! isset($nutrientRules[$code])) {
                continue;
            }
            [$field, $op, $val, $excludeNull] = $nutrientRules[$code];
            $extract = "JSON_EXTRACT(nutrition_snapshot, '$.{$field}')";
            if ($excludeNull) {
                $query->whereRaw("JSON_TYPE({$extract}) NOT IN ('NULL')");
            }
            $query->whereRaw("CAST({$extract} AS DECIMAL(10,2)) {$op} ?", [$val]);
        }
        // Trang Nguyên liệu (UI-017): chỉ nguyên liệu đã có số + sắp xếp theo kcal.
        if ($request->boolean('with_nutrition')) {
            // kcal để null trong JSON vẫn là một giá trị JSON null chứ không
            // phải SQL NULL — phải kiểm cả JSON_TYPE mới lọc đúng.
            $query->whereRaw("JSON_TYPE(JSON_EXTRACT(nutrition_snapshot, '$.kcal_per_100g')) NOT IN ('NULL')");
        }
        match ($request->query('sort')) {
            // reorder(): bỏ orderBy('name_vi') mặc định phía trên, nếu không
            // thứ tự theo tên sẽ lấn át thứ tự kcal.
            'kcal_asc', 'kcal_desc' => $query->reorder()
                ->orderByRaw("COALESCE(JSON_TYPE(JSON_EXTRACT(nutrition_snapshot, '$.kcal_per_100g')) IN ('NULL'), 1)")
                ->orderByRaw("CAST(JSON_EXTRACT(nutrition_snapshot, '$.kcal_per_100g') AS DECIMAL(10,2)) ".($request->query('sort') === 'kcal_asc' ? 'ASC' : 'DESC'))
                ->orderBy('name_vi'),
            default => null,
        };
        $page = $query->paginate(min(60, max(1, (int) $request->query('per_page', 50))));
        // IMG-001: ảnh chính của nguyên liệu (nếu có).
        $page->getCollection()->transform(function (Ingredient $ing) {
            $ing->setAttribute('image', $ing->primaryImage());
            $ing->setAttribute('unverified', SourceTier::isSecondary($ing->nutrition_snapshot['fct_source'] ?? null));

            return $ing;
        });

        return response()->json($page);
    }

    /** Chi tiết một nguyên liệu (UI-017): dinh dưỡng/100g, nhãn, món có dùng. */
    public function ingredient(Ingredient $ingredient): JsonResponse
    {
        $ingredient->load(['tags:id,slug,name', 'aliases:id,ingredient_id,name,note', 'media', 'tcmTraits', 'components:id,slug,name_vi']);
        $ingredient->setAttribute('image', $ingredient->primaryImage());
        $ingredient->setAttribute('unverified', SourceTier::isSecondary($ingredient->nutrition_snapshot['fct_source'] ?? null));
        // TCM-000: khối "Tính vị:" (mọi dòng nhãn kèm nguồn, kể cả mâu thuẫn)
        // và liên kết thành phần cho nguyên liệu hỗn hợp — trình bày qua
        // TcmDisplay rồi gỡ relation thô để JSON chỉ có một khuôn duy nhất.
        $ingredient->setAttribute('tcm', TcmDisplay::forIngredient($ingredient));
        // FOOD-COMBINE-001: lớp thông tin "Kết hợp cần lưu ý" (chỉ hiển thị,
        // không lọc/chấm điểm — xem App\Combinations\CombinationDisplay).
        $ingredient->setAttribute('combinations', CombinationDisplay::forItem($ingredient->slug));
        $ingredient->setAttribute('component_links', $ingredient->components
            ->map(fn ($c) => ['slug' => $c->slug, 'name_vi' => $c->name_vi])
            ->values()->all());
        $ingredient->unsetRelation('tcmTraits');
        $ingredient->unsetRelation('components');
        $fruit = Fruit::where('ingredient_id', $ingredient->id)->first(['slug']);
        $dishes = Dish::query()
            ->whereHas('dishIngredients', fn ($w) => $w->where('ingredient_id', $ingredient->id))
            ->with([
                'names' => fn ($q) => $q->where('is_primary', true)->orderByRaw("CASE WHEN locale = 'vi' THEN 0 ELSE 1 END"),
                'nutrition', 'media',
            ])
            ->limit(12)
            ->get()
            ->map(fn (Dish $d) => [
                'slug' => $d->slug,
                'name' => $d->names->first()?->name,
                'image' => $d->primaryImage(),
                'nutrition' => $d->nutrition === null ? null : [
                    'kcal_min' => $d->nutrition->kcal_min,
                    'kcal_max' => $d->nutrition->kcal_max,
                    'confidence' => $d->nutrition->confidence,
                ],
            ]);

        return response()->json([
            'ingredient' => $ingredient,
            'fruit_slug' => $fruit?->slug,
            'dishes' => $dishes,
        ]);
    }
}
