<?php

declare(strict_types=1);

namespace App\Http\Controllers\PublicWeb;

use App\Combinations\CombinationDisplay;
use App\Cultural\TcmDisplay;
use App\Http\Controllers\Controller;
use App\Models\Vegetable;
use App\Nutrition\SourceTier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API công khai cho trang rau theo mùa (VEGETABLE-MODULE, theo mẫu Fruit):
 * danh sách lọc theo tháng + vùng (Bắc/Trung/Nam), nhóm, nguồn cung, trạng
 * thái và tìm kiếm theo cả tên gọi khác (aliases trong file catalogue).
 * Chi tiết kèm dinh dưỡng từ nguyên liệu liên kết (nếu có), cờ "chưa kiểm
 * định" và cặp kết hợp đã thẩm định chạm tới rau này (chỉ hiển thị).
 */
class VegetableController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // Theo mẫu Fruit: kèm media để lấy ảnh chính, kèm nutrition_snapshot
        // để tính cờ "chưa kiểm định" cho thẻ danh sách (không đổi dữ liệu).
        $query = Vegetable::query()
            ->with(['ingredient:id,slug,name_vi,name_en,nutrition_snapshot', 'ingredient.media'])
            ->orderBy('slug');

        if ($q = trim((string) $request->query('q', ''))) {
            // Aliases nằm trong cột JSON mảng chuỗi nên tìm bằng PHP trên một
            // lần lấy toàn bộ (99 mục), thay vì whereJsonContains (chỉ khớp đúng).
            $aliasSlugs = Vegetable::query()->pluck('aliases', 'slug')
                ->filter(fn ($aliases) => collect($aliases ?? [])
                    ->contains(fn ($a) => mb_stripos((string) $a, $q) !== false))
                ->keys()->all();
            $query->where(function ($w) use ($q, $aliasSlugs) {
                $w->where('name_vi', 'like', "%{$q}%")
                    ->orWhereHas('ingredient', fn ($i) => $i->where('name_vi', 'like', "%{$q}%")
                        ->orWhere('name_en', 'like', "%{$q}%"));
                if ($aliasSlugs !== []) {
                    $w->orWhereIn('slug', $aliasSlugs);
                }
            });
        }
        if ($group = $request->query('group')) {
            $query->where('group', $group);
        }
        if ($supply = $request->query('supply')) {
            $query->where('supply', $supply);
        }
        if ($state = $request->query('state')) {
            $query->where('state', $state);
        }
        // Tháng có bán: theo vùng đã chọn, hoặc bất kỳ vùng nào nếu không chọn vùng.
        // Dạng chế biến (muối/khô/chua) bán quanh năm nên luôn khớp lọc tháng.
        if ($month = (int) $request->query('month', 0)) {
            $region = $request->query('region');
            $column = match ($region) {
                'bac' => 'months_bac',
                'trung' => 'months_trung',
                'nam' => 'months_nam',
                default => null,
            };
            $query->where(function ($w) use ($month, $column) {
                $w->where('state', '!=', 'tuoi');
                if ($column !== null) {
                    $w->orWhereJsonContains($column, $month);
                } else {
                    $w->orWhereJsonContains('months_bac', $month)
                        ->orWhereJsonContains('months_trung', $month)
                        ->orWhereJsonContains('months_nam', $month);
                }
            });
        }
        // Chỉ chính vụ trong tháng đã chọn.
        if ($request->query('main_season') === '1' && ($month = (int) $request->query('month', 0))) {
            $query->whereJsonContains('main_season_months', $month);
        }

        $perPage = min(120, max(1, (int) $request->query('per_page', 99)));
        $vegetables = $query->paginate($perPage);
        $vegetables->getCollection()->transform(fn (Vegetable $v) => $this->summary($v));

        return response()->json($vegetables);
    }

    public function show(Vegetable $vegetable): JsonResponse
    {
        // Theo mẫu Fruit: kèm aliases + tags để trang chi tiết vẽ được khối
        // dị ứng và dòng tên gọi khác của nguyên liệu liên kết.
        $vegetable->load('ingredient.aliases:id,ingredient_id,name,note', 'ingredient.tags:id,slug,name', 'ingredient.media', 'ingredient.tcmTraits');
        $ingredient = $vegetable->ingredient;
        $snapshot = $ingredient?->nutrition_snapshot ?? [];

        $combinations = collect([
            ...CombinationDisplay::forItem($vegetable->slug),
            ...($ingredient ? CombinationDisplay::forItem($ingredient->slug) : []),
        ])->unique('slug')->values()->all();

        return response()->json([
            'vegetable' => [
                ...$this->summary($vegetable),
                'months_bac' => $vegetable->months_bac,
                'months_trung' => $vegetable->months_trung,
                'months_nam' => $vegetable->months_nam,
                'region_notes' => $vegetable->region_notes ?? [],
                'best_months' => $vegetable->best_months ?? [],
                'selection_note' => $vegetable->selection_note,
                'storage_note' => $vegetable->storage_note,
                'cooking_note' => $vegetable->cooking_note,
                'safety_note' => $vegetable->safety_note,
                'uncertain' => $vegetable->uncertain,
                'uncertain_note' => $vegetable->uncertain_note,
                'fresh_counterpart_slug' => $vegetable->fresh_counterpart_slug,
                'source_note' => $vegetable->source_note,
                'needs_review' => $vegetable->needs_review,
            ],
            'ingredient' => $ingredient ? [
                'id' => $ingredient->id,
                'slug' => $ingredient->slug,
                'name_vi' => $ingredient->name_vi,
                'name_en' => $ingredient->name_en,
                'tcm' => TcmDisplay::forIngredient($ingredient),
                'aliases' => $ingredient->aliases->map(fn ($a) => ['name' => $a->name, 'note' => $a->note])->all(),
                'tags' => $ingredient->tags->map(fn ($t) => ['slug' => $t->slug, 'name' => $t->name])->all(),
                'nutrition' => [
                    'kcal_per_100g' => $snapshot['kcal_per_100g'] ?? null,
                    'protein_g' => $snapshot['protein_g'] ?? null,
                    'fat_g' => $snapshot['fat_g'] ?? null,
                    'carb_g' => $snapshot['carb_g'] ?? null,
                    'fiber_g' => $snapshot['fiber_g'] ?? null,
                    'sodium_mg' => $snapshot['sodium_mg'] ?? null,
                    'fct_source' => $snapshot['fct_source'] ?? null,
                    'scientific_name' => $snapshot['scientific_name'] ?? null,
                    'unverified' => SourceTier::isSecondary($snapshot['fct_source'] ?? null),
                ],
            ] : null,
            'combinations' => $combinations,
            'combinations_disclaimer' => CombinationDisplay::DISCLAIMER,
        ]);
    }

    /** @return array<string, mixed> */
    private function summary(Vegetable $vegetable): array
    {
        $ingredient = $vegetable->ingredient;
        $snapshot = $ingredient?->nutrition_snapshot ?? [];

        return [
            'id' => $vegetable->id,
            'slug' => $vegetable->slug,
            'name_vi' => $vegetable->name_vi,
            'ingredient_slug' => $ingredient?->slug,
            'aliases' => $vegetable->aliases ?? [],
            'group' => $vegetable->group,
            'supply' => $vegetable->supply,
            'state' => $vegetable->state,
            'months_bac' => $vegetable->months_bac,
            'months_trung' => $vegetable->months_trung,
            'months_nam' => $vegetable->months_nam,
            'region_notes' => $vegetable->region_notes ?? [],
            'main_season_months' => $vegetable->main_season_months ?? [],
            'best_months' => $vegetable->best_months ?? [],
            'kcal_per_100g' => $vegetable->kcal_per_100g,
            'kcal_confidence' => $vegetable->kcal_confidence,
            'uncertain' => $vegetable->uncertain,
            'uncertain_note' => $vegetable->uncertain_note,
            'image' => $ingredient?->primaryImage(),
            // Cờ "chưa kiểm định" theo mẫu Fruit (nguồn thứ cấp) — chỉ có
            // ý nghĩa khi có nguyên liệu liên kết, rau chưa có hồ sơ thì false.
            'unverified' => $ingredient ? SourceTier::isSecondary($snapshot['fct_source'] ?? null) : false,
            'needs_review' => $vegetable->needs_review,
        ];
    }
}
