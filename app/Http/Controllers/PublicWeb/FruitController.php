<?php

declare(strict_types=1);

namespace App\Http\Controllers\PublicWeb;

use App\Http\Controllers\Controller;
use App\Models\Fruit;
use App\Nutrition\SourceTier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API công khai cho trang hoa quả (FRUIT-006): danh sách có lọc theo tháng
 * (đang vào mùa / hời nhất tháng này), nhóm, nguồn gốc và tìm kiếm theo cả
 * tên gọi khác của quả (ALIAS-001); chi tiết kèm dinh dưỡng từ nguyên liệu
 * liên kết, cờ "chưa kiểm định" và bảng giá theo miền.
 */
class FruitController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Fruit::query()
            ->with(['ingredient:id,slug,name_vi,name_en', 'ingredient.aliases:id,ingredient_id,name,note', 'ingredient.media'])
            ->orderBy('slug');

        if ($q = trim((string) $request->query('q', ''))) {
            $query->whereHas('ingredient', fn ($w) => $w->where('name_vi', 'like', "%{$q}%")
                ->orWhere('name_en', 'like', "%{$q}%")
                ->orWhereHas('aliases', fn ($a) => $a->where('name', 'like', "%{$q}%")));
        }
        if ($group = $request->query('group')) {
            $query->where('classification_group', $group);
        }
        if ($origin = $request->query('origin')) {
            $query->where('origin', $origin);
        }
        // Tháng có hàng (JSON contains trên cột tháng).
        if ($month = (int) $request->query('month', 0)) {
            $query->whereJsonContains('availability_months', $month);
        }
        // Tháng hời nhất (nằm trong cửa sổ giá hời).
        if ($valueMonth = (int) $request->query('value_month', 0)) {
            $query->whereJsonContains('best_value_months', $valueMonth);
        }

        $fruits = $query->paginate(60);

        $fruits->getCollection()->transform(fn (Fruit $fruit) => $this->summary($fruit));

        return response()->json($fruits);
    }

    public function show(Fruit $fruit): JsonResponse
    {
        $fruit->load([
            'ingredient.aliases:id,ingredient_id,name,note',
            'ingredient.tags:id,slug,name',
            'ingredient.media',
            'ingredient.tcmTraits',
            'prices' => fn ($q) => $q->orderBy('region_code')->orderBy('month'),
        ]);
        $ingredient = $fruit->ingredient;
        $snapshot = $ingredient->nutrition_snapshot ?? [];

        return response()->json([
            'fruit' => [
                ...$this->summary($fruit),
                'family' => $fruit->family,
                'grown_in' => $fruit->grown_in ?? [],
                'availability_type' => $fruit->availability_type,
                'main_season_months' => $fruit->main_season_months ?? [],
                'best_nutrition' => ['months' => $fruit->best_nutrition_months ?? [], 'note' => $fruit->best_nutrition_note],
                'best_value' => ['months' => $fruit->best_value_months ?? [], 'note' => $fruit->best_value_note],
                'pairing_cautions' => $fruit->pairing_cautions ?? [],
                'cultural_note' => $fruit->cultural_note,
                'selection_note' => $fruit->selection_note,
                'storage_note' => $fruit->storage_note,
                'needs_review' => $fruit->needs_review,
            ],
            'ingredient' => [
                'slug' => $ingredient->slug,
                'name_vi' => $ingredient->name_vi,
                'name_en' => $ingredient->name_en,
                // TCM-000: nhãn tính vị của nguyên liệu quả (kèm nguồn).
                'tcm' => \App\Cultural\TcmDisplay::forIngredient($ingredient),
                'aliases' => $ingredient->aliases->map(fn ($a) => ['name' => $a->name, 'note' => $a->note])->all(),
                'tags' => $ingredient->tags->map(fn ($t) => ['slug' => $t->slug, 'name' => $t->name])->all(),
                'nutrition' => [
                    'kcal_per_100g' => $snapshot['kcal_per_100g'] ?? null,
                    'kcal_min' => $snapshot['kcal_min'] ?? null,
                    'kcal_max' => $snapshot['kcal_max'] ?? null,
                    'protein_g' => $snapshot['protein_g'] ?? null,
                    'fat_g' => $snapshot['fat_g'] ?? null,
                    'carb_g' => $snapshot['carb_g'] ?? null,
                    'fiber_g' => $snapshot['fiber_g'] ?? null,
                    'sodium_mg' => $snapshot['sodium_mg'] ?? null,
                    'fct_source' => $snapshot['fct_source'] ?? null,
                    'scientific_name' => $snapshot['scientific_name'] ?? null,
                    // Số liệu từ nguồn thứ cấp (tầng 6) = "chưa kiểm định".
                    'unverified' => SourceTier::isSecondary($snapshot['fct_source'] ?? null),
                ],
            ],
            // Giá tham khảo mùa vụ (FRUIT-MODULE §3) — không phải giá theo ngày.
            'prices' => $fruit->prices->map(fn ($p) => [
                'region_code' => $p->region_code,
                'month' => $p->month,
                'price_min' => $p->price_min,
                'price_max' => $p->price_max,
                'source_name' => $p->source_name,
                'source_url' => $p->source_url,
                'collected_at' => $p->collected_at?->toDateString(),
            ])->all(),
        ]);
    }

    /** @return array<string, mixed> */
    private function summary(Fruit $fruit): array
    {
        $ingredient = $fruit->ingredient;
        $snapshot = $ingredient->nutrition_snapshot ?? [];

        return [
            'id' => $fruit->id,
            'slug' => $fruit->slug,
            'name_vi' => $ingredient->name_vi,
            'name_en' => $ingredient->name_en,
            'aliases' => $ingredient->aliases->map(fn ($a) => ['name' => $a->name, 'note' => $a->note])->all(),
            'classification_group' => $fruit->classification_group,
            'origin' => $fruit->origin,
            'availability_months' => $fruit->availability_months ?? [],
            'main_season_months' => $fruit->main_season_months ?? [],
            'best_nutrition_months' => $fruit->best_nutrition_months ?? [],
            'best_value_months' => $fruit->best_value_months ?? [],
            'kcal_per_100g' => $snapshot['kcal_per_100g'] ?? null,
            // IMG-001: ảnh của quả = ảnh của nguyên liệu liên kết.
            'image' => $ingredient->primaryImage(),
            'unverified' => SourceTier::isSecondary($snapshot['fct_source'] ?? null),
            'needs_review' => $fruit->needs_review,
        ];
    }
}
