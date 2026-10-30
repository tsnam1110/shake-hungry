<?php

declare(strict_types=1);

use App\Models\Dish;
use App\Models\Ingredient;
use App\Recommendation\Recommender;
use Illuminate\Support\Facades\DB;

/* REC-001: engine gợi ý 3 tầng — an toàn dị ứng là bất biến cao nhất. */

function recDish(string $slug, array $opts = []): Dish
{
    $dish = Dish::create([
        'slug' => $slug, 'country_code' => 'VN', 'status' => 'draft',
        'source' => 'curated', 'confidence' => 'low', 'needs_review' => true,
        'completeness_level' => 1,
        'cooking_method' => $opts['method'] ?? 'nau',
        'serving_grams' => $opts['serving'] ?? 300,
        'parent_dish_id' => $opts['parent'] ?? null,
    ]);
    $dish->names()->create(['locale' => 'vi', 'name' => $opts['name'] ?? $slug, 'is_primary' => true, 'is_alias' => false]);
    foreach ($opts['slots'] ?? ['trua'] as $slot) {
        $dish->mealSlots()->create(['slot' => $slot, 'weight' => 8]);
    }
    foreach ($opts['ingredients'] ?? [] as $ingSlug) {
        $ing = Ingredient::where('slug', $ingSlug)->first()
            ?? Ingredient::create([
                'name_vi' => $ingSlug, 'slug' => $ingSlug, 'category' => $opts['category'] ?? 'khac',
                'mapping_confidence' => 'high', 'source' => 'curated', 'confidence' => 'high',
            ]);
        $dish->dishIngredients()->create(['ingredient_id' => $ing->id, 'quantity' => 100, 'unit_name' => 'g', 'source' => 'curated']);
    }
    if ($opts['nutrition'] ?? true) {
        DB::table('dish_nutrition')->insert([
            'dish_id' => $dish->id, 'basis' => 'per_serving',
            'kcal_min' => 300, 'kcal_max' => 500, 'protein_g' => 20, 'fat_g' => 10, 'carb_g' => 40,
            'confidence' => 'medium', 'fct_source' => 'usda_sr_legacy',
            'missing_ingredients' => json_encode($opts['missing'] ?? []),
            'computed_at' => now(), 'engine_version' => '1.1.0', 'source' => 'computed',
        ]);
    }

    return $dish->fresh();
}

function shakeIt(array $overrides = []): array
{
    return app(Recommender::class)->shake(array_merge([
        'slot' => 'trua', 'user_id' => null, 'shake_index' => 0,
        'allergens' => [], 'recent_dish_ids' => [],
    ], $overrides));
}

it('lắc ra món có dữ liệu dinh dưỡng và ghi log kèm seed', function () {
    recDish('mon-a');
    $r = shakeIt();
    expect($r['dish'])->not->toBeNull()
        ->and($r['seed'])->toBeString()
        ->and(DB::table('recommendation_logs')->count())->toBe(1);
});

it('không gợi ý món chứa chất gây dị ứng, kể cả khi nới lỏng hết cỡ', function () {
    recDish('mon-lac', ['ingredients' => ['lac-rang']]);
    recDish('mon-thieu-data', ['missing' => ['nghe-tuoi']]);
    $r = shakeIt(['allergens' => ['lac-rang']]);
    expect($r['dish'])->toBeNull()
        ->and($r['suggestion'])->not->toBeNull();
});

it('món thiếu dữ liệu nguyên liệu vẫn gợi ý được cho người không khai dị ứng', function () {
    recDish('mon-thieu-data', ['missing' => ['nghe-tuoi']]);
    $r = shakeIt();
    expect($r['dish']?->slug)->toBe('mon-thieu-data');
});

it('loại trừ món vừa ăn gần nhất ở mọi mức nới lỏng', function () {
    $a = recDish('mon-a');
    $r = shakeIt(['recent_dish_ids' => [$a->id]]);
    expect($r['dish'])->toBeNull();
});

it('lọc chay loại món có thịt/hải sản', function () {
    recDish('mon-thit', ['ingredients' => ['thit-lon-xay'], 'category' => 'thit']);
    recDish('mon-rau', ['ingredients' => ['rau-muong'], 'category' => 'rau-cu']);
    $r = shakeIt(['vegetarian' => true]);
    expect($r['dish']?->slug)->toBe('mon-rau');
});

it('cùng seed và ngữ cảnh thì ra cùng món (dựng lại được)', function () {
    recDish('mon-a');
    recDish('mon-b');
    recDish('mon-c');
    $first = shakeIt(['shake_index' => 3])['dish']?->id;
    $second = shakeIt(['shake_index' => 3])['dish']?->id;
    expect($first)->toBe($second)->not->toBeNull();
});

it('bữa chính không gợi ý gia vị khẩu phần nhỏ, bữa ăn vặt thì có', function () {
    recDish('nuoc-mam-test', ['serving' => 15, 'slots' => ['trua', 'an-vat']]);
    expect(shakeIt(['slot' => 'trua'])['dish'])->toBeNull();
    expect(shakeIt(['slot' => 'an-vat'])['dish']?->slug)->toBe('nuoc-mam-test');
});

it('chi tiết món có khối minh bạch nguồn số (UI-003) và lọc ăn ngoài hoạt động (UI-002)', function () {
    $dish = recDish('mon-chi-tiet', ['name' => 'Món chi tiết']);
    $resp = $this->getJson('/api/dishes/mon-chi-tiet')->assertOk();
    expect($resp->json('provenance.fct_source'))->toBe('usda_sr_legacy')
        ->and($resp->json('provenance.computed_at'))->not->toBeNull()
        ->and($resp->json('dish.names.0.name'))->toBe('Món chi tiết');

    $home = recDish('mon-tu-nau');
    $home->update(['is_eat_out' => false, 'is_home_cook' => true]);
    $out = recDish('mon-an-ngoai');
    $out->update(['is_eat_out' => true, 'is_home_cook' => false]);
    $r = app(Recommender::class)->shake([
        'slot' => 'trua', 'user_id' => null, 'shake_index' => 0,
        'allergens' => [], 'recent_dish_ids' => [$dish->id, $home->id], 'eat_out' => true,
    ]);
    expect($r['dish']?->id)->toBe($out->id);
});
