<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\Fruit;
use App\Models\Ingredient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Quản lý hồ sơ hoa quả trong trang quản trị (FRUIT-005) — mục riêng
 * /admin/hoa-qua. Kỷ luật dữ liệu theo FRUIT-MODULE.md: cửa sổ phải nằm
 * trong tháng có hàng; mỗi dòng giá bắt buộc nguồn + ngày thu thập (V17).
 * Dinh dưỡng/dị ứng KHÔNG sửa ở đây — chúng thuộc nguyên liệu liên kết.
 */
class FruitController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Fruit::query()->with(['ingredient:id,slug,name_vi', 'prices:id,fruit_id']);
        if ($q = trim((string) $request->query('q', ''))) {
            $query->whereHas('ingredient', fn ($w) => $w->where('name_vi', 'like', "%{$q}%")
                ->orWhere('slug', 'like', "%{$q}%")
                ->orWhereHas('aliases', fn ($a) => $a->where('name', 'like', "%{$q}%")));
        }
        if ($request->has('needs_review')) {
            $query->where('needs_review', $request->boolean('needs_review'));
        }
        if ($group = $request->query('group')) {
            $query->where('classification_group', $group);
        }
        $query->orderBy('slug');

        return response()->json($query->paginate(min(100, max(1, (int) $request->query('per_page', 20)))));
    }

    public function show(Fruit $fruit): JsonResponse
    {
        return response()->json($fruit->load([
            'ingredient:id,slug,name_vi,name_en',
            'ingredient.aliases:id,ingredient_id,name,note',
            'prices' => fn ($q) => $q->orderBy('region_code')->orderBy('month'),
        ]));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validatePayload($request);
        $ingredient = Ingredient::findOrFail($data['ingredient_id']);
        $fruit = Fruit::create([
            ...$this->profileFields($data),
            'slug' => $this->uniqueSlug($ingredient->slug),
            'ingredient_id' => $ingredient->id,
            'needs_review' => true,
        ]);
        $this->syncPrices($fruit, $data['prices'] ?? []);
        AdminAuditLog::record('create', $fruit, ['slug' => $fruit->slug]);

        return response()->json($this->show($fruit)->getData(), 201);
    }

    public function update(Request $request, Fruit $fruit): JsonResponse
    {
        $data = $this->validatePayload($request, $fruit);
        $before = $fruit->only(['classification_group', 'origin', 'availability_months']);
        $fruit->update($this->profileFields($data));
        if ($request->has('prices')) {
            $this->syncPrices($fruit, $data['prices'] ?? []);
        }
        AdminAuditLog::record('update', $fruit, ['before' => $before, 'after' => $data]);

        return $this->show($fruit);
    }

    public function destroy(Fruit $fruit): JsonResponse
    {
        $fruit->delete();
        AdminAuditLog::record('delete', $fruit, ['slug' => $fruit->slug]);

        return response()->json(null, 204);
    }

    public function review(Request $request, Fruit $fruit): JsonResponse
    {
        $data = $request->validate([
            'approve' => ['required', 'boolean'],
            'review_note' => ['nullable', 'string', 'max:1000'],
        ]);
        if ($data['approve']) {
            $fruit->update([
                'needs_review' => false,
                'reviewed_by' => $request->user()->email,
                'reviewed_at' => now()->toDateString(),
                'review_note' => $data['review_note'] ?? $fruit->review_note,
            ]);
            AdminAuditLog::record('approve', $fruit);
        } else {
            $fruit->update(['needs_review' => true]);
            AdminAuditLog::record('reopen', $fruit);
        }

        return $this->show($fruit);
    }

    /** @param array<string, mixed> $data */
    private function profileFields(array $data): array
    {
        return [
            'classification_group' => $data['classification_group'],
            'family' => $data['family'] ?? null,
            'origin' => $data['origin'],
            'grown_in' => $data['grown_in'] ?? null,
            'availability_months' => $data['availability_months'],
            'main_season_months' => $data['main_season_months'] ?? null,
            'availability_type' => $data['availability_type'],
            'best_nutrition_months' => $data['best_nutrition_months'] ?? null,
            'best_nutrition_note' => $data['best_nutrition_note'] ?? null,
            'best_value_months' => $data['best_value_months'] ?? null,
            'best_value_note' => $data['best_value_note'] ?? null,
            'pairing_cautions' => $data['pairing_cautions'] ?? null,
            'selection_note' => $data['selection_note'] ?? null,
            'storage_note' => $data['storage_note'] ?? null,
        ];
    }

    private function validatePayload(Request $request, ?Fruit $existing = null): array
    {
        $data = $request->validate([
            'ingredient_id' => [
                $existing === null ? 'required' : 'sometimes', 'integer',
                Rule::exists('ingredients', 'id')->where('category', 'trai-cay'),
                $existing === null ? Rule::unique('fruits', 'ingredient_id') : Rule::unique('fruits', 'ingredient_id')->ignore($existing->id),
            ],
            'classification_group' => ['required', Rule::in(['co-mui', 'mong', 'hach', 'nhiet-doi', 'dua', 'khac'])],
            'family' => ['nullable', 'string', 'max:255'],
            'origin' => ['required', Rule::in(['trong-nuoc', 'nhap-khau', 'ca-hai'])],
            'grown_in' => ['nullable', 'array'],
            'grown_in.*.region' => ['required', 'string', 'max:255'],
            'grown_in.*.note' => ['nullable', 'string', 'max:255'],
            'availability_months' => ['required', 'array', 'min:1'],
            'availability_months.*' => ['integer', 'min:1', 'max:12', 'distinct'],
            'main_season_months' => ['nullable', 'array'],
            'main_season_months.*' => ['integer', 'min:1', 'max:12', 'distinct'],
            'availability_type' => ['required', Rule::in(['theo-mua', 'quanh-nam', 'quanh-nam-nhap-khau'])],
            'best_nutrition_months' => ['nullable', 'array'],
            'best_nutrition_months.*' => ['integer', 'min:1', 'max:12', 'distinct'],
            'best_nutrition_note' => ['nullable', 'string'],
            'best_value_months' => ['nullable', 'array'],
            'best_value_months.*' => ['integer', 'min:1', 'max:12', 'distinct'],
            'best_value_note' => ['nullable', 'string'],
            'pairing_cautions' => ['nullable', 'array'],
            'pairing_cautions.*.with' => ['required', 'string', 'max:255'],
            'pairing_cautions.*.level' => ['required', Rule::in(['co-so', 'dan-gian'])],
            'pairing_cautions.*.note' => ['required', 'string'],
            'selection_note' => ['nullable', 'string'],
            'storage_note' => ['nullable', 'string'],
            'prices' => ['nullable', 'array'],
            'prices.*.region_code' => ['required', Rule::in(['bac', 'trung', 'nam'])],
            'prices.*.month' => ['required', 'integer', 'min:1', 'max:12'],
            'prices.*.price_min' => ['required', 'integer', 'min:1'],
            'prices.*.price_max' => ['required', 'integer', 'gte:prices.*.price_min'],
            'prices.*.source_name' => ['required', 'string', 'max:255'],
            'prices.*.source_url' => ['nullable', 'string', 'max:1000'],
            'prices.*.collected_at' => ['required', 'date_format:Y-m-d'],
        ]);

        // V17: vụ chính + 2 cửa sổ phải nằm trong tháng có hàng; giá chỉ ở tháng có hàng.
        $months = $data['availability_months'];
        foreach (['main_season_months', 'best_nutrition_months', 'best_value_months'] as $field) {
            $extra = array_diff($data[$field] ?? [], $months);
            if ($extra !== []) {
                throw ValidationException::withMessages([$field => 'Tháng '.implode(', ', $extra).' nằm ngoài tháng có hàng.']);
            }
        }
        $seen = [];
        foreach ($data['prices'] ?? [] as $i => $row) {
            if (! in_array($row['month'], $months, true)) {
                throw ValidationException::withMessages(["prices.{$i}.month" => 'Giá chỉ điền cho tháng có hàng.']);
            }
            $key = $row['region_code'].'-'.$row['month'];
            if (isset($seen[$key])) {
                throw ValidationException::withMessages(["prices.{$i}.month" => 'Trùng dòng giá miền × tháng.']);
            }
            $seen[$key] = true;
        }

        return $data;
    }

    private function syncPrices(Fruit $fruit, array $prices): void
    {
        $fruit->prices()->delete();
        foreach ($prices as $row) {
            $fruit->prices()->create([
                'region_code' => $row['region_code'],
                'month' => $row['month'],
                'price_min' => $row['price_min'],
                'price_max' => $row['price_max'],
                'source_name' => $row['source_name'],
                'source_url' => $row['source_url'] ?? null,
                'collected_at' => $row['collected_at'],
            ]);
        }
    }

    private function uniqueSlug(string $base): string
    {
        $slug = Str::slug($base) ?: 'qua';
        $candidate = $slug;
        $i = 2;
        while (Fruit::where('slug', $candidate)->exists()) {
            $candidate = "{$slug}-{$i}";
            $i++;
        }

        return $candidate;
    }
}
