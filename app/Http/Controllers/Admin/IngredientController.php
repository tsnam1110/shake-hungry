<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\Ingredient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Quản lý nguyên liệu trong trang quản trị (ADM-002).
 * Ràng buộc dữ liệu R6–R10 vẫn do DB thực thi; API này soi lại R7 (ghi chú
 * ánh xạ) ở tầng form để người biên soạn nhận lỗi thân thiện trước.
 */
class IngredientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Ingredient::query()->with('referenceFood:id,name_en,kcal_per_100g');
        if ($q = trim((string) $request->query('q', ''))) {
            $query->where(function ($w) use ($q) {
                $w->where('name_vi', 'like', "%{$q}%")
                    ->orWhere('name_en', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%")
                    // Tên gọi khác / tên vùng miền (ALIAS-001).
                    ->orWhereHas('aliases', fn ($a) => $a->where('name', 'like', "%{$q}%"));
            });
        }
        if ($request->has('needs_review')) {
            $query->where('needs_review', $request->boolean('needs_review'));
        }
        if ($category = $request->query('category')) {
            $query->where('category', $category);
        }
        $query->orderBy('name_vi');

        return response()->json($query->paginate(min(100, max(1, (int) $request->query('per_page', 20)))));
    }

    public function show(Ingredient $ingredient): JsonResponse
    {
        return response()->json($ingredient->load(['units', 'referenceFood', 'aliases']));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validatePayload($request);
        $data['source'] = $data['source'] ?? 'curated';
        $data['confidence'] = $request->input('confidence', 'unknown');
        $data['slug'] = $this->uniqueSlug($data['name_en'] ?? $data['name_vi']);
        $data['needs_review'] = true;
        $ingredient = Ingredient::create($data);
        $this->syncUnits($ingredient, $request->input('units', []));
        if ($request->has('aliases')) {
            $this->syncAliases($ingredient, $request->input('aliases', []));
        }
        AdminAuditLog::record('create', $ingredient, ['name_vi' => $ingredient->name_vi]);

        return response()->json($ingredient->load(['units', 'referenceFood', 'aliases']), 201);
    }

    public function update(Request $request, Ingredient $ingredient): JsonResponse
    {
        $data = $this->validatePayload($request, $ingredient);
        $before = $ingredient->only(array_keys($data));
        $ingredient->update($data);
        if ($request->has('units')) {
            $this->syncUnits($ingredient, $request->input('units', []));
        }
        if ($request->has('aliases')) {
            $this->syncAliases($ingredient, $request->input('aliases', []));
        }
        AdminAuditLog::record('update', $ingredient, ['before' => $before, 'after' => $data]);

        return response()->json($ingredient->load(['units', 'referenceFood', 'aliases']));
    }

    public function destroy(Ingredient $ingredient): JsonResponse
    {
        try {
            $ingredient->delete();
        } catch (\Throwable) {
            return response()->json([
                'message' => 'Không xoá được: nguyên liệu đang được dùng trong món ăn hoặc có dữ liệu con.',
            ], 422);
        }
        AdminAuditLog::record('delete', $ingredient, ['name_vi' => $ingredient->name_vi]);

        return response()->json(['ok' => true]);
    }

    /** Duyệt (R10): hạ cờ needs_review và ghi vết người duyệt; hoặc mở lại để duyệt lại. */
    public function review(Request $request, Ingredient $ingredient): JsonResponse
    {
        $data = $request->validate([
            'approve' => ['required', 'boolean'],
            'review_note' => ['nullable', 'string', 'max:1000'],
        ]);
        if ($data['approve']) {
            $ingredient->update([
                'needs_review' => false,
                'reviewed_by' => $request->user()->email,
                'reviewed_at' => now(),
                'review_note' => $data['review_note'] ?? $ingredient->review_note,
            ]);
            AdminAuditLog::record('approve', $ingredient);
        } else {
            $ingredient->update(['needs_review' => true]);
            AdminAuditLog::record('reopen', $ingredient);
        }

        return response()->json($ingredient->load(['units', 'referenceFood']));
    }

    private function validatePayload(Request $request, ?Ingredient $existing = null): array
    {
        return $request->validate([
            'name_vi' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:64'],
            'subcategory' => ['nullable', 'string', 'max:64'],
            'edible_portion_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'density_g_per_ml' => ['nullable', 'numeric', 'min:0'],
            'default_yield_factor' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'oil_absorption_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_ready_to_eat' => ['nullable', 'boolean'],
            'reference_food_id' => ['nullable', 'integer', 'exists:reference_foods,id'],
            'mapping_confidence' => ['required', Rule::in(['high', 'medium', 'low'])],
            'confidence' => ['nullable', Rule::in(['high', 'medium', 'low', 'unknown'])],
            // R7 soi ở tầng form: độ tin cậy ánh xạ khác high thì bắt buộc ghi chú.
            'mapping_note' => [
                Rule::requiredIf(fn () => $request->filled('mapping_confidence') && $request->input('mapping_confidence') !== 'high'),
                'nullable', 'string',
            ],
            'source' => ['nullable', Rule::in(['curated', 'llm_suggested', 'imported', 'user_submitted'])],
            'confidence_note' => ['nullable', 'string'],
            // Tên gọi khác / tên vùng miền (ALIAS-001).
            'aliases' => ['nullable', 'array'],
            'aliases.*.name' => ['required', 'string', 'max:255'],
            'aliases.*.note' => ['nullable', 'string', 'max:255'],
        ]);
    }

    /** Đồng bộ tên gọi khác (ALIAS-001) từ form quản trị. */
    private function syncAliases(Ingredient $ingredient, array $aliases): void
    {
        $ingredient->aliases()->delete();
        foreach ($aliases as $alias) {
            if (trim((string) ($alias['name'] ?? '')) === '') {
                continue;
            }
            $ingredient->aliases()->create([
                'name' => trim((string) $alias['name']),
                'note' => isset($alias['note']) && trim((string) $alias['note']) !== '' ? trim((string) $alias['note']) : null,
            ]);
        }
    }

    /** Đồng bộ bảng con ingredient_units từ form (ADM-002 mục 2). */
    private function syncUnits(Ingredient $ingredient, array $units): void
    {
        $keepIds = [];
        foreach ($units as $unit) {
            if (empty($unit['unit_name'])) {
                continue;
            }
            $row = $ingredient->units()->updateOrCreate(
                ['id' => $unit['id'] ?? 0],
                [
                    'unit_name' => $unit['unit_name'],
                    'grams' => $unit['grams'] ?? null,
                    'note' => $unit['note'] ?? null,
                    'source' => $unit['source'] ?? 'curated',
                ],
            );
            $keepIds[] = $row->id;
        }
        $ingredient->units()->whereNotIn('id', $keepIds ?: [0])->delete();
    }

    private function uniqueSlug(string $base): string
    {
        $slug = Str::slug($base) ?: 'nguyen-lieu';
        $candidate = $slug;
        $i = 2;
        while (Ingredient::where('slug', $candidate)->exists()) {
            $candidate = $slug.'-'.$i++;
        }

        return $candidate;
    }
}
