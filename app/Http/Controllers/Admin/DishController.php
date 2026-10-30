<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\Dish;
use App\Models\DishName;
use App\Nutrition\DishNutritionService;
use App\Support\NameSimilarity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Quản lý món ăn trong trang quản trị: form nhập Mức 1 (ADM-003),
 * cảnh báo trùng tên bằng trigram (ADM-005), bảng nguyên liệu của món và
 * nút tính dinh dưỡng bằng engine chính thức (không tự chế công thức).
 */
class DishController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Dish::query()->with(['names' => fn ($q) => $q->where('is_primary', true), 'region:id,name']);
        if ($q = trim((string) $request->query('q', ''))) {
            $query->whereHas('names', fn ($w) => $w->where('name', 'like', "%{$q}%"));
        }
        if ($request->has('needs_review')) {
            $query->where('needs_review', $request->boolean('needs_review'));
        }
        if ($request->filled('completeness_level')) {
            $query->where('completeness_level', (int) $request->query('completeness_level'));
        }
        $query->latest('id');

        return response()->json($query->paginate(min(100, max(1, (int) $request->query('per_page', 20)))));
    }

    public function show(Dish $dish): JsonResponse
    {
        return response()->json($this->present($dish));
    }

    private function present(Dish $dish): Dish
    {
        return $dish->load([
            'names', 'region', 'mealSlots',
            'dishIngredients.ingredient:id,name_vi,slug',
            'nutrition',
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validatePayload($request);
        $dish = Dish::create([
            'country_code' => 'VN',
            ...$data['dish'],
            'slug' => $this->uniqueSlug($data['primary_name']),
            'status' => 'draft',
            'source' => 'curated',
            'confidence' => 'unknown',
            'needs_review' => true,
        ]);
        $this->syncNames($dish, $data);
        $this->syncSlots($dish, $data['meal_slots'] ?? []);
        $this->syncIngredients($dish, $data['ingredients'] ?? []);
        AdminAuditLog::record('create', $dish, ['name' => $data['primary_name']]);

        return response()->json($this->present($dish), 201);
    }

    public function update(Request $request, Dish $dish): JsonResponse
    {
        $data = $this->validatePayload($request);
        $dish->update($data['dish']);
        $this->syncNames($dish, $data);
        if (array_key_exists('meal_slots', $data)) {
            $this->syncSlots($dish, $data['meal_slots'] ?? []);
        }
        if (array_key_exists('ingredients', $data)) {
            $this->syncIngredients($dish, $data['ingredients'] ?? []);
        }
        AdminAuditLog::record('update', $dish);

        return response()->json($this->present($dish->fresh()));
    }

    public function destroy(Dish $dish): JsonResponse
    {
        $dish->delete();
        AdminAuditLog::record('delete', $dish);

        return response()->json(['ok' => true]);
    }

    /** Duyệt món (cùng cơ chế vết duyệt R10 như nguyên liệu). */
    public function review(Request $request, Dish $dish): JsonResponse
    {
        $data = $request->validate([
            'approve' => ['required', 'boolean'],
            'review_note' => ['nullable', 'string', 'max:1000'],
        ]);
        if ($data['approve']) {
            $dish->update([
                'needs_review' => false,
                'reviewed_by' => $request->user()->email,
                'reviewed_at' => now(),
                'review_note' => $data['review_note'] ?? $dish->review_note,
            ]);
            AdminAuditLog::record('approve', $dish);
        } else {
            $dish->update(['needs_review' => true]);
            AdminAuditLog::record('reopen', $dish);
        }

        return response()->json($this->present($dish->fresh()));
    }

    /** ADM-005: món có tên giống tên đang gõ (độ giống trigram > 0,4). */
    public function similar(Request $request): JsonResponse
    {
        $name = trim((string) $request->query('name', ''));
        $excludeId = (int) $request->query('exclude_id', 0);
        if ($name === '') {
            return response()->json([]);
        }
        $results = [];
        DishName::query()->where('is_primary', true)
            ->chunk(200, function ($names) use ($name, $excludeId, &$results) {
                foreach ($names as $dishName) {
                    if ($dishName->dish_id === $excludeId) {
                        continue;
                    }
                    $score = NameSimilarity::similarity($name, $dishName->name);
                    if ($score > 0.4) {
                        $results[] = [
                            'dish_id' => $dishName->dish_id,
                            'name' => $dishName->name,
                            'region' => null,
                            'similarity' => round($score, 3),
                        ];
                    }
                }
            });
        if ($results !== []) {
            $regions = Dish::with('region:id,name')
                ->whereIn('id', array_column($results, 'dish_id'))
                ->get()
                ->mapWithKeys(fn (Dish $d) => [$d->id => $d->region?->name]);
            foreach ($results as &$r) {
                $r['region'] = $regions[$r['dish_id']] ?? null;
            }
            unset($r);
        }
        usort($results, fn ($a, $b) => $b['similarity'] <=> $a['similarity']);

        return response()->json(array_slice($results, 0, 10));
    }

    /** Tính dinh dưỡng bằng engine chính thức rồi lưu vào dish_nutrition. */
    public function compute(Dish $dish, DishNutritionService $service): JsonResponse
    {
        if ($dish->dishIngredients()->count() === 0) {
            return response()->json(['message' => 'Món chưa có nguyên liệu nào — nhập nguyên liệu trước khi tính.'], 422);
        }
        $row = $service->computeAndStore($dish, $service->pickFctSource($dish));
        if ($row === null) {
            return response()->json(['message' => 'Engine chưa tính được (thiếu dữ liệu ánh xạ của nguyên liệu).'], 422);
        }
        AdminAuditLog::record('compute', $dish, ['kcal' => $row->kcal_point ?? null]);

        return response()->json($row);
    }

    private function validatePayload(Request $request): array
    {
        $v = $request->validate([
            'primary_name' => ['required', 'string', 'max:255'],
            'alias_names' => ['nullable', 'array'],
            'alias_names.*' => ['string', 'max:255'],
            'english_name' => ['nullable', 'string', 'max:255'],
            'country_code' => ['nullable', 'string', 'size:2'],
            'region_id' => ['nullable', 'integer', 'exists:regions,id'],
            'is_eat_out' => ['nullable', 'boolean'],
            'is_home_cook' => ['nullable', 'boolean'],
            'cooking_method' => ['nullable', 'string', 'max:32'],
            'serving_desc' => ['nullable', 'string', 'max:255'],
            'serving_grams' => ['nullable', 'numeric', 'min:0'],
            'parent_dish_id' => ['nullable', 'integer', 'exists:dishes,id'],
            'recipe_steps' => ['nullable', 'array'],
            'recipe_steps.*' => ['string'],
            'meal_slots' => ['nullable', 'array'],
            'meal_slots.*' => [Rule::in(['sang', 'trua', 'chieu', 'toi', 'an-vat'])],
            'ingredients' => ['nullable', 'array'],
            'ingredients.*.ingredient_id' => ['required_with:ingredients', 'integer', 'exists:ingredients,id'],
            'ingredients.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'ingredients.*.unit_name' => ['nullable', 'string', 'max:32'],
            'ingredients.*.note' => ['nullable', 'string'],
            'ingredients.*.is_optional' => ['nullable', 'boolean'],
        ]);

        return [
            'primary_name' => $v['primary_name'],
            'alias_names' => $v['alias_names'] ?? [],
            'english_name' => $v['english_name'] ?? null,
            'meal_slots' => $v['meal_slots'] ?? null,
            'ingredients' => $v['ingredients'] ?? null,
            'dish' => collect($v)->only([
                'country_code', 'region_id', 'is_eat_out', 'is_home_cook',
                'cooking_method', 'serving_desc', 'serving_grams',
                'parent_dish_id', 'recipe_steps',
            ])->all(),
        ];
    }

    private function syncNames(Dish $dish, array $data): void
    {
        $dish->names()->updateOrCreate(
            ['is_primary' => true, 'locale' => 'vi'],
            ['name' => $data['primary_name'], 'is_alias' => false],
        );
        $dish->names()->where('locale', 'en')->delete();
        if (! empty($data['english_name'])) {
            $dish->names()->create([
                'locale' => 'en', 'name' => $data['english_name'],
                'is_primary' => false, 'is_alias' => false,
            ]);
        }
        $dish->names()->where('is_alias', true)->delete();
        foreach ($data['alias_names'] ?? [] as $alias) {
            if (trim($alias) !== '') {
                $dish->names()->create([
                    'locale' => 'vi', 'name' => $alias,
                    'is_primary' => false, 'is_alias' => true,
                ]);
            }
        }
    }

    private function syncSlots(Dish $dish, array $slots): void
    {
        $dish->mealSlots()->delete();
        foreach (array_unique($slots) as $slot) {
            $dish->mealSlots()->create(['slot' => $slot, 'weight' => 5]);
        }
    }

    private function syncIngredients(Dish $dish, array $rows): void
    {
        $dish->dishIngredients()->delete();
        foreach ($rows as $row) {
            $dish->dishIngredients()->create([
                'ingredient_id' => $row['ingredient_id'],
                'quantity' => $row['quantity'] ?? null,
                'unit_name' => $row['unit_name'] ?? 'g',
                'note' => $row['note'] ?? null,
                'is_optional' => $row['is_optional'] ?? false,
                'source' => 'curated',
            ]);
        }
    }

    private function uniqueSlug(string $name): string
    {
        $slug = Str::slug(NameSimilarity::normalize($name)) ?: 'mon-an';
        $candidate = $slug;
        $i = 2;
        while (Dish::where('slug', $candidate)->exists()) {
            $candidate = $slug.'-'.$i++;
        }

        return $candidate;
    }
}
