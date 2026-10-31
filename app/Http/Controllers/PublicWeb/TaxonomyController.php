<?php

declare(strict_types=1);

namespace App\Http\Controllers\PublicWeb;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\Tag;
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
        $query = Ingredient::query()->with(['tags:id,slug,name', 'aliases:id,ingredient_id,name,note'])->orderBy('name_vi');
        if ($q = trim((string) $request->query('q', ''))) {
            // Tìm cả tên gọi khác / tên vùng miền (ALIAS-001).
            $query->where(fn ($w) => $w->where('name_vi', 'like', "%{$q}%")
                ->orWhereHas('aliases', fn ($a) => $a->where('name', 'like', "%{$q}%")));
        }
        if ($category = $request->query('category')) {
            $query->where('category', $category);
        }
        foreach (\App\Taxonomy\TagFilter::slugsByFacet($request) as $slugs) {
            $query->whereHas('tags', fn ($w) => $w->whereIn('tags.slug', $slugs));
        }

        return response()->json($query->paginate(50));
    }
}
