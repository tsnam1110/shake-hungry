<?php

declare(strict_types=1);

use App\Models\Dish;
use App\Models\Ingredient;
use App\Models\Tag;
use App\Models\User;
use App\Recommendation\Recommender;
use Illuminate\Support\Facades\DB;

/*
 * REC-004: tuỳ chọn "Bữa cơm bình dân" cho mâm cơm — chỉ lấy món dễ nấu
 * (≤8 bước) mà mọi nguyên liệu đều phổ biến trong kho (xuất hiện ở ≥4 món:
 * đồ chợ nào cũng có), loại món có nguyên liệu hiếm/đặc sản hoặc cách làm
 * cầu kỳ. Tiêu chí SUY từ dữ liệu kho, không gán tay từng món.
 */

function bdDish(string $slug, array $opts = []): Dish
{
    $dish = Dish::create([
        'slug' => $slug, 'country_code' => 'VN', 'status' => 'draft',
        'source' => 'curated', 'confidence' => 'low', 'needs_review' => true,
        'completeness_level' => 1,
        'cooking_method' => $opts['method'] ?? 'nau',
        'serving_grams' => 300,
        'recipe_steps' => array_map(fn ($i) => ['step' => $i + 1, 'text' => 'Bước '.($i + 1)], range(0, ($opts['steps'] ?? 3) - 1)),
        'is_eat_out' => false, 'is_home_cook' => true,
    ]);
    $dish->names()->create(['locale' => 'vi', 'name' => $opts['name'] ?? $slug, 'is_primary' => true, 'is_alias' => false]);
    $dish->mealSlots()->create(['slot' => 'trua', 'weight' => 8]);
    foreach ($opts['ingredients'] ?? [] as [$ingSlug, $category, $qty]) {
        $ing = Ingredient::where('slug', $ingSlug)->first()
            ?? Ingredient::create([
                'name_vi' => $ingSlug, 'slug' => $ingSlug, 'category' => $category,
                'mapping_confidence' => 'high', 'source' => 'curated', 'confidence' => 'high',
            ]);
        $dish->dishIngredients()->create(['ingredient_id' => $ing->id, 'quantity' => $qty, 'unit_name' => 'g', 'source' => 'curated']);
    }
    foreach ($opts['tags'] ?? [] as $tagSlug) {
        $dish->tags()->attach(Tag::firstOrCreate(['slug' => $tagSlug], ['name' => $tagSlug])->id);
    }
    DB::table('dish_nutrition')->insert([
        'dish_id' => $dish->id, 'basis' => 'per_serving',
        'kcal_min' => 300, 'kcal_max' => 500, 'protein_g' => 20, 'fat_g' => 10, 'carb_g' => 40,
        'confidence' => 'medium', 'fct_source' => 'usda_sr_legacy',
        'missing_ingredients' => json_encode([]),
        'computed_at' => now(), 'engine_version' => '1.1.0', 'source' => 'computed',
    ]);

    return $dish->fresh();
}

/**
 * Thế giới bình dân: 4 nguyên liệu dùng chung (mỗi thứ có mặt ở ≥4 món) và
 * 9 món thường ngày dùng chúng; cộng 1 món có nguyên liệu hiếm (chỉ 1 món
 * dùng) và 1 món cầu kỳ 12 bước dù nguyên liệu phổ biến.
 */
function bdFixtures(): void
{
    $mc = ['loai:mon-chinh'];
    $canh = ['loai:canh'];
    bdDish('bd-thit-kho', ['tags' => $mc, 'ingredients' => [['bd-thit', 'thit', 200], ['bd-gia-vi', 'gia-vi', 10]]]);
    bdDish('bd-ca-kho', ['tags' => $mc, 'ingredients' => [['bd-ca', 'thuy-san', 200], ['bd-gia-vi', 'gia-vi', 10]]]);
    bdDish('bd-canh-rau', ['tags' => $canh, 'ingredients' => [['bd-rau', 'rau-cu', 150], ['bd-thit', 'thit', 30], ['bd-gia-vi', 'gia-vi', 5]]]);
    bdDish('bd-rau-xao', ['ingredients' => [['bd-rau', 'rau-cu', 200], ['bd-gia-vi', 'gia-vi', 10]]]);
    bdDish('bd-thit-xao-rau', ['tags' => $mc, 'ingredients' => [['bd-thit', 'thit', 180], ['bd-rau', 'rau-cu', 50], ['bd-gia-vi', 'gia-vi', 10]]]);
    bdDish('bd-canh-ca', ['tags' => $canh, 'ingredients' => [['bd-rau', 'rau-cu', 120], ['bd-ca', 'thuy-san', 40], ['bd-gia-vi', 'gia-vi', 5]]]);
    bdDish('bd-thit-luoc', ['tags' => $mc, 'ingredients' => [['bd-thit', 'thit', 150], ['bd-gia-vi', 'gia-vi', 10]]]);
    bdDish('bd-canh-ca-rau', ['tags' => $canh, 'ingredients' => [['bd-ca', 'thuy-san', 60], ['bd-rau', 'rau-cu', 60], ['bd-gia-vi', 'gia-vi', 5]]]);
    bdDish('bd-ca-hap', ['tags' => $mc, 'ingredients' => [['bd-ca', 'thuy-san', 170], ['bd-gia-vi', 'gia-vi', 10]]]);
    // Ngoài vùng bình dân:
    bdDish('bd-mon-la', ['tags' => $mc, 'ingredients' => [['bd-thit', 'thit', 200], ['bd-hat-doi', 'gia-vi', 5]]]);
    bdDish('bd-mon-cau-ky', ['tags' => $mc, 'steps' => 12, 'ingredients' => [['bd-thit', 'thit', 200], ['bd-gia-vi', 'gia-vi', 10]]]);
}

test('mâm bình dân chỉ gồm món nguyên liệu phổ biến và dễ nấu, vẫn đủ vai trò', function () {
    bdFixtures();
    $seen = [];
    foreach (range(0, 5) as $i) {
        $resp = $this->postJson('/api/shake', [
            'slot' => 'trua', 'mode' => 'home', 'dish_count' => 3,
            'binh_dan' => true, 'shake_index' => $i,
        ])->assertOk()->assertJsonPath('binh_dan', true);
        expect($resp->json('dishes'))->toHaveCount(3);
        foreach ($resp->json('dishes') as $d) {
            $seen[] = $d['slug'];
        }
    }
    expect($seen)->not->toContain('bd-mon-la')       // nguyên liệu hiếm (tần suất 1)
        ->and($seen)->not->toContain('bd-mon-cau-ky'); // 12 bước nấu
});

test('tắt bình dân thì hai món kia trở lại kho lựa chọn của mâm', function () {
    bdFixtures();
    // Kiểm ở tầng engine cho tất định: kho mâm không lọc bình dân chứa cả 11 món.
    $rec = app(Recommender::class);
    $off = $rec->shakeTray(['slot' => 'trua', 'dish_count' => 3, 'binh_dan' => false]);
    expect($off['binh_dan'])->toBeFalse()
        ->and($off['pool_size'])->toBe(11);
    $on = $rec->shakeTray(['slot' => 'trua', 'dish_count' => 3, 'binh_dan' => true]);
    expect($on['binh_dan'])->toBeTrue()
        ->and($on['pool_size'])->toBe(9);
});

test('hồ sơ tray_binh_dan được dùng khi lắc không truyền tham số, và ghi qua API hồ sơ', function () {
    bdFixtures();
    $user = User::factory()->create();
    $user->profile()->create(['tray_binh_dan' => true]);

    $resp = $this->actingAs($user)->postJson('/api/shake', [
        'slot' => 'trua', 'mode' => 'home', 'dish_count' => 3,
    ])->assertOk()->assertJsonPath('binh_dan', true);
    $slugs = array_column($resp->json('dishes'), 'slug');
    expect($slugs)->not->toContain('bd-mon-la');

    $this->actingAs($user)->putJson('/api/profile', ['tray_binh_dan' => false])->assertOk();
    expect($user->profile()->first()->tray_binh_dan)->toBeFalse();
    $user->unsetRelation('profile'); // quan hệ đã nạp bị cache trên instance dùng chung của test
    $this->actingAs($user)->postJson('/api/shake', [
        'slot' => 'trua', 'mode' => 'home', 'dish_count' => 3,
    ])->assertOk()->assertJsonPath('binh_dan', false);
});
