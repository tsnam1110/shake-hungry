<?php

declare(strict_types=1);

/*
 * REC-005: đổi từng món trong mâm — ngẫu nhiên hoặc chọn từ danh sách ứng
 * viên; vẫn giữ đúng vai trò và lọc cứng dị ứng của lắc mâm.
 */

function swapDish(string $slug, array $opts = []): \App\Models\Dish
{
    $dish = \App\Models\Dish::create([
        'slug' => $slug, 'country_code' => 'VN', 'status' => 'draft',
        'source' => 'curated', 'confidence' => 'low', 'needs_review' => true,
        'completeness_level' => 1,
        'cooking_method' => $opts['method'] ?? 'nau',
        'serving_grams' => $opts['serving'] ?? 300,
        'is_eat_out' => $opts['eat_out'] ?? false,
        'is_home_cook' => $opts['home_cook'] ?? true,
    ]);
    $dish->names()->create(['locale' => 'vi', 'name' => $opts['name'] ?? $slug, 'is_primary' => true, 'is_alias' => false]);
    $dish->mealSlots()->create(['slot' => 'trua', 'weight' => 8]);
    foreach ($opts['ingredients'] ?? [] as [$ingSlug, $category, $qty]) {
        $ing = \App\Models\Ingredient::where('slug', $ingSlug)->first()
            ?? \App\Models\Ingredient::create([
                'name_vi' => $ingSlug, 'slug' => $ingSlug, 'category' => $category,
                'mapping_confidence' => 'high', 'source' => 'curated', 'confidence' => 'high',
            ]);
        $dish->dishIngredients()->create(['ingredient_id' => $ing->id, 'quantity' => $qty, 'unit_name' => 'g', 'source' => 'curated']);
    }
    foreach ($opts['tags'] ?? [] as $tagSlug) {
        $tag = \App\Models\Tag::firstOrCreate(['slug' => $tagSlug], ['name' => $tagSlug]);
        $dish->tags()->attach($tag->id);
    }
    \Illuminate\Support\Facades\DB::table('dish_nutrition')->insert([
        'dish_id' => $dish->id, 'basis' => 'per_serving',
        'kcal_min' => 300, 'kcal_max' => 500, 'protein_g' => 20, 'fat_g' => 10, 'carb_g' => 40,
        'confidence' => 'medium', 'fct_source' => 'usda_sr_legacy',
        'missing_ingredients' => json_encode([]),
        'computed_at' => now(), 'engine_version' => '1.1.0', 'source' => 'computed',
    ]);

    return $dish->fresh();
}

/** Mâm mẫu: 2 món chính đạm + 1 canh + 1 món rau + 1 món cơm (bị loại khỏi mâm). */
function swapFixtures(): void
{
    swapDish('thit-kho', ['tags' => ['loai:mon-chinh'], 'ingredients' => [['thit-lon', 'thit', 200]]]);
    swapDish('ca-kho', ['tags' => ['loai:mon-chinh'], 'ingredients' => [['ca-loc', 'thuy-san', 200]]]);
    swapDish('canh-bi', ['tags' => ['loai:canh'], 'ingredients' => [['bi-xanh', 'rau-cu', 200]]]);
    swapDish('rau-xao', ['tags' => [], 'ingredients' => [['rau-muong', 'rau-cu', 200]]]);
    swapDish('com-trang', ['tags' => ['loai:com'], 'ingredients' => [['gao', 'tinh-bot', 200]]]);
}

it('ứng viên đổi món: đúng vai trò, loại trừ món đang có trong mâm', function () {
    swapFixtures();
    $ids = [\App\Models\Dish::where('slug', 'thit-kho')->first()->id,
        \App\Models\Dish::where('slug', 'canh-bi')->first()->id,
        \App\Models\Dish::where('slug', 'rau-xao')->first()->id];

    $dishes = $this->getJson('/api/shake/tray-candidates?slot=trua&role=chinh&exclude_dish_ids[]=' . implode('&exclude_dish_ids[]=', $ids))
        ->assertOk()->json('dishes');
    $slugs = collect($dishes)->pluck('slug')->all();
    expect($slugs)->toContain('ca-kho')->not->toContain('thit-kho');
});

it('đổi ngẫu nhiên trả món khác cùng vai trò, không trùng mâm', function () {
    swapFixtures();
    $tray = $this->postJson('/api/shake', ['slot' => 'trua', 'mode' => 'home', 'dish_count' => 3, 'shake_index' => 0])
        ->assertOk()->json('dishes');
    $ids = collect($tray)->pluck('id')->all();
    $canhId = collect($tray)->firstWhere('role', 'canh')['id'];

    // Kho test chỉ có 1 canh — đổi canh phải báo không còn món (dish = null)
    $this->postJson('/api/shake/tray-swap', [
        'slot' => 'trua', 'role' => 'canh', 'exclude_dish_ids' => $ids, 'shake_index' => 5,
    ])->assertOk()->assertJsonPath('dish', null);

    // Đổi món chính: còn ca-kho ngoài mâm
    $swap = $this->postJson('/api/shake/tray-swap', [
        'slot' => 'trua', 'role' => 'chinh', 'exclude_dish_ids' => $ids, 'shake_index' => 5,
    ])->assertOk()->json('dish');
    expect($swap)->not->toBeNull()
        ->and($swap['role'])->toBe('chinh')
        ->and($ids)->not->toContain($swap['id']);
});

it('đổi món vẫn giữ lọc cứng dị ứng', function () {
    swapFixtures();
    swapDish('canh-tom', ['tags' => ['loai:canh'], 'ingredients' => [['tom-tuoi', 'thuy-san', 150]]]);
    $ids = [\App\Models\Dish::where('slug', 'thit-kho')->first()->id];

    // Dị ứng tôm: ứng viên canh chỉ còn canh-bi, không có canh-tom
    $dishes = $this->getJson('/api/shake/tray-candidates?slot=trua&role=canh&allergens[]=tom-tuoi&exclude_dish_ids[]=' . $ids[0])
        ->assertOk()->json('dishes');
    $slugs = collect($dishes)->pluck('slug')->all();
    expect($slugs)->toContain('canh-bi')->not->toContain('canh-tom');
});
