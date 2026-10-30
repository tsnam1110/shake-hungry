<?php

declare(strict_types=1);

use App\Models\Dish;
use App\Models\DishIngredient;
use App\Models\DishMedia;
use App\Models\DishNutrition;
use App\Models\Ingredient;
use App\Models\IngredientTcmTrait;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * TEST-001 — chứng minh 10 ràng buộc R1–R10 (DATA-MODEL §6) THỰC SỰ chặn được
 * ở tầng DATABASE, không phải tầng validation của Laravel: mục đích là chặn
 * cả khi code có lỗi.
 */
function makeIngredient(array $overrides = []): Ingredient
{
    return Ingredient::create(array_merge([
        'slug' => 'test-ing-'.uniqid(),
        'name_vi' => 'Nguyên liệu test',
        'mapping_confidence' => 'high',
        'category' => 'khac',
        'source' => 'curated',
        'confidence' => 'high',
    ], $overrides));
}

function makeDish(array $overrides = []): Dish
{
    return Dish::create(array_merge([
        'slug' => 'test-dish-'.uniqid(),
        'country_code' => 'VN',
        'completeness_level' => 1,
        'source' => 'curated',
        'confidence' => 'medium',
    ], $overrides));
}

// R1 — chốt chặn kỹ thuật cho QT-1 (CLAUDE.md §3): LLM bị CẤM sinh con số
// dinh dưỡng; database từ chối kể cả khi tầng ứng dụng có lỗi.
it('R1: chan ban ghi dinh duong do LLM sinh ra', function () {
    $dish = makeDish();

    expect(fn () => DishNutrition::create([
        'dish_id' => $dish->id,
        'basis' => 'per_serving',
        'kcal_min' => 300, 'kcal_max' => 400,
        'confidence' => 'medium',
        'fct_source' => 'usda_sr_legacy',
        'computed_at' => now(),
        'engine_version' => '1.0.0',
        'source' => 'llm_suggested', // vi phạm R1
    ]))->toThrow(QueryException::class);
});

it('R2: chan kcal_min > kcal_max', function () {
    $dish = makeDish();

    expect(fn () => DishNutrition::create([
        'dish_id' => $dish->id,
        'basis' => 'per_serving',
        'kcal_min' => 500, 'kcal_max' => 400, // vi phạm R2
        'confidence' => 'medium',
        'fct_source' => 'usda_sr_legacy',
        'computed_at' => now(),
        'engine_version' => '1.0.0',
        'source' => 'computed',
    ]))->toThrow(QueryException::class);
});

it('R3: chan confidence=high khi missing_ingredients khac rong', function () {
    $dish = makeDish();

    expect(fn () => DishNutrition::create([
        'dish_id' => $dish->id,
        'basis' => 'per_serving',
        'kcal_min' => 300, 'kcal_max' => 400,
        'confidence' => 'high', // vi phạm R3 vì missing khác rỗng
        'fct_source' => 'usda_sr_legacy',
        'missing_ingredients' => ['banh-pho'],
        'computed_at' => now(),
        'engine_version' => '1.0.0',
        'source' => 'computed',
    ]))->toThrow(QueryException::class);
});

it('R4: chan anh khong co license', function () {
    $dish = makeDish();

    expect(fn () => DishMedia::create([
        'dish_id' => $dish->id,
        'path' => 'dishes/test.webp',
        'license' => null, // vi phạm R4
    ]))->toThrow(QueryException::class);
});

it('R5: chan nhan tinh vi khong co nguon', function () {
    $ing = makeIngredient();

    expect(fn () => IngredientTcmTrait::create([
        'ingredient_id' => $ing->id,
        'nature' => 'han',
        'source_ref' => null, // vi phạm R5
        'confidence' => 'low',
    ]))->toThrow(QueryException::class);
});

it('R6: chan mon Muc 2 khong co nguyen lieu nao', function () {
    expect(function () {
        $dish = makeDish(['completeness_level' => 2]); // không có dish_ingredients
        // Ép kiểm R6/R8 ngay (cơ chế theo nền tảng — xem forceCompletenessCheck):
        forceCompletenessCheck($dish->id);
    })->toThrow(QueryException::class);
});

it('R7: chan mapping_confidence khac high ma khong co mapping_note', function () {
    expect(fn () => makeIngredient([
        'mapping_confidence' => 'medium',
        'mapping_note' => null, // vi phạm R7
    ]))->toThrow(QueryException::class);
});

it('R8: chan mon Muc 2 co it hon 3 nguon cong thuc', function () {
    expect(function () {
        $ing = makeIngredient();
        $dish = makeDish(['completeness_level' => 2]);
        DishIngredient::create([
            'dish_id' => $dish->id,
            'ingredient_id' => $ing->id,
            'quantity' => 100, 'unit_name' => 'g', 'source' => 'curated',
        ]);
        // Đủ R6 (1 nguyên liệu) nhưng 0 nguồn công thức — vi phạm R8
        forceCompletenessCheck($dish->id);
    })->toThrow(QueryException::class, 'R8');
});

it('R9: chan dish_ingredients tro toi nguyen lieu khong ton tai', function () {
    $dish = makeDish();

    expect(fn () => DishIngredient::create([
        'dish_id' => $dish->id,
        'ingredient_id' => 999999999, // vi phạm R9
        'quantity' => 100, 'unit_name' => 'g', 'source' => 'curated',
    ]))->toThrow(QueryException::class);
});

// R10 — ING-004: `needs_review = false` là lời khẳng định "đã có người duyệt".
// Trước 2026-09-01 cờ này hạ được mà không để lại dấu vết nào, nên cả 255/255
// nguyên liệu kẹt ở true vĩnh viễn. Ràng buộc này bắt cờ phải đi kèm bằng chứng.
it('R10: chan ha needs_review ma khong ghi ai duyet va duyet khi nao', function () {
    expect(fn () => makeIngredient(['needs_review' => false]))
        ->toThrow(QueryException::class);

    expect(fn () => makeDish(['needs_review' => false]))
        ->toThrow(QueryException::class);
});

it('R10: chan nua bo dau vet duyet (co nguoi duyet nhung khong co thoi diem)', function () {
    expect(fn () => makeIngredient(['reviewed_by' => 'tampv.bg@gmail.com']))
        ->toThrow(QueryException::class);

    expect(fn () => makeIngredient(['reviewed_at' => now()]))
        ->toThrow(QueryException::class);
});

it('R10: cho phep ha needs_review khi da ghi du dau vet', function () {
    $ing = makeIngredient([
        'needs_review' => false,
        'reviewed_by' => 'tampv.bg@gmail.com',
        'reviewed_at' => now(),
        'review_note' => 'Đối chiếu lại USDA fdc_id 174036, khớp.',
    ]);

    expect($ing->fresh()->needs_review)->toBeFalse();
});

it('cho phep ban ghi hop le di qua — cac rang buoc khong chan nham', function () {
    $ing = makeIngredient();
    $dish = makeDish(['completeness_level' => 2]);
    DishIngredient::create([
        'dish_id' => $dish->id, 'ingredient_id' => $ing->id,
        'quantity' => 100, 'unit_name' => 'g', 'source' => 'curated',
    ]);
    foreach ([1, 2, 3] as $i) {
        $dish->recipeSources()->create([
            'url' => "https://example.test/nguon-{$i}",
            'accessed_at' => '2026-08-31',
            'note' => 'nguồn kiểm thử',
        ]);
    }
    DishNutrition::create([
        'dish_id' => $dish->id,
        'basis' => 'per_serving',
        'kcal_min' => 300, 'kcal_max' => 400,
        'confidence' => 'medium',
        'fct_source' => 'usda_sr_legacy',
        'computed_at' => now(),
        'engine_version' => '1.0.0',
        'source' => 'computed',
    ]);
    forceCompletenessCheck($dish->id);

    expect(DishNutrition::where('dish_id', $dish->id)->exists())->toBeTrue();
});
