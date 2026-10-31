<?php

declare(strict_types=1);

use App\Models\Dish;
use App\Models\Ingredient;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/*
 * REC-003: lắc hai chế độ — ăn ngoài mặc định 1 món; mâm cơm tự nấu chọn
 * 1–4 món (không tính cơm), mặc định 3 món theo vai trò mâm cơm chuẩn
 * (món chính đạm + canh + rau). Dị ứng vẫn là lọc cứng ở cả hai chế độ.
 */

function trayDish(string $slug, array $opts = []): Dish
{
    $dish = Dish::create([
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
        $ing = Ingredient::where('slug', $ingSlug)->first()
            ?? Ingredient::create([
                'name_vi' => $ingSlug, 'slug' => $ingSlug, 'category' => $category,
                'mapping_confidence' => 'high', 'source' => 'curated', 'confidence' => 'high',
            ]);
        $dish->dishIngredients()->create(['ingredient_id' => $ing->id, 'quantity' => $qty, 'unit_name' => 'g', 'source' => 'curated']);
    }
    foreach ($opts['tags'] ?? [] as $tagSlug) {
        $tag = Tag::firstOrCreate(['slug' => $tagSlug], ['name' => $tagSlug]);
        $dish->tags()->attach($tag->id);
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

/** Mâm mẫu: 2 món chính đạm + 1 canh + 1 món rau + 1 món cơm (phải bị loại khỏi mâm). */
function trayFixtures(): void
{
    trayDish('thit-kho', ['tags' => ['loai:mon-chinh'], 'ingredients' => [['thit-lon', 'thit', 200]]]);
    trayDish('ca-kho', ['tags' => ['loai:mon-chinh'], 'ingredients' => [['ca-loc', 'thuy-san', 200]]]);
    trayDish('canh-bi', ['tags' => ['loai:canh'], 'ingredients' => [['bi-xanh', 'rau-cu', 200]]]);
    trayDish('rau-xao', ['tags' => [], 'ingredients' => [['rau-muong', 'rau-cu', 200]]]);
    trayDish('com-trang', ['tags' => ['loai:com'], 'ingredients' => [['gao', 'tinh-bot', 200]]]);
}

test('mâm cơm mặc định 3 món đúng vai trò chính + canh + rau, không có cơm', function () {
    trayFixtures();

    $resp = $this->postJson('/api/shake', ['slot' => 'trua', 'mode' => 'home']);
    $resp->assertOk()
        ->assertJsonPath('mode', 'home')
        ->assertJsonPath('dish_count', 3)
        ->assertJsonCount(3, 'dishes');

    $roles = array_column($resp->json('dishes'), 'role');
    expect($roles)->toBe(['chinh', 'canh', 'rau']);
    $slugs = array_column($resp->json('dishes'), 'slug');
    expect($slugs)->not->toContain('com-trang');
    expect($resp->json('tray_kcal.kcal_min'))->toBeGreaterThan(0);
});

test('mâm cơm chọn được 1, 2 và 4 món; món không trùng nhau', function () {
    trayFixtures();

    $one = $this->postJson('/api/shake', ['slot' => 'trua', 'mode' => 'home', 'dish_count' => 1, 'shake_index' => 1]);
    $one->assertOk()->assertJsonCount(1, 'dishes')
        ->assertJsonPath('dishes.0.role', 'chinh');

    $two = $this->postJson('/api/shake', ['slot' => 'trua', 'mode' => 'home', 'dish_count' => 2, 'shake_index' => 2]);
    expect(array_column($two->json('dishes'), 'role'))->toBe(['chinh', 'canh']);

    $four = $this->postJson('/api/shake', ['slot' => 'trua', 'mode' => 'home', 'dish_count' => 4, 'shake_index' => 3]);
    $four->assertOk()->assertJsonCount(4, 'dishes');
    expect(array_column($four->json('dishes'), 'role'))->toBe(['chinh', 'canh', 'rau', 'phu']);
    $ids = array_column($four->json('dishes'), 'id');
    expect(array_unique($ids))->toHaveCount(4);
});

test('chế độ ăn ngoài chỉ lắc đúng 1 món ăn ngoài', function () {
    trayFixtures();
    trayDish('pho-quan', ['eat_out' => true, 'home_cook' => false, 'tags' => ['loai:bun-pho-mi'], 'ingredients' => [['thit-bo', 'thit', 150]]]);

    $resp = $this->postJson('/api/shake', ['slot' => 'trua', 'mode' => 'eat_out']);
    $resp->assertOk()
        ->assertJsonPath('mode', 'eat_out')
        ->assertJsonPath('dish_count', 1)
        ->assertJsonCount(1, 'dishes')
        ->assertJsonPath('dish.slug', 'pho-quan');
});

test('mâm cơm không bao giờ chứa món có nguyên liệu dị ứng', function () {
    trayDish('tom-rang', ['tags' => ['loai:mon-chinh'], 'ingredients' => [['tom-su', 'thuy-san', 200]]]);
    trayDish('canh-rau', ['tags' => ['loai:canh'], 'ingredients' => [['rau-muong', 'rau-cu', 200]]]);
    trayDish('rau-luoc', ['tags' => [], 'ingredients' => [['bi-xanh', 'rau-cu', 200]]]);

    $resp = $this->postJson('/api/shake', [
        'slot' => 'trua', 'mode' => 'home', 'dish_count' => 3, 'allergens' => ['tom-su'],
    ]);
    $resp->assertOk();
    expect(array_column($resp->json('dishes'), 'slug'))->not->toContain('tom-rang');
});

test('mâm chay loại món bị gắn nhãn diet:man dù nguyên liệu thuộc nhóm gia vị', function () {
    // Cà chấm mắm tôm: mắm tôm là gia vị (lọc theo nhóm nguyên liệu lọt qua),
    // nhưng nhãn diet suy từ thành phần là "man" — không được vào mâm chay.
    trayDish('ca-cham-mam', ['tags' => ['diet:man'], 'ingredients' => [['ca-phao', 'rau-cu', 150], ['mam-tom', 'gia-vi', 20]]]);
    trayDish('dau-phu-sot', ['tags' => ['loai:mon-chinh', 'diet:chay'], 'ingredients' => [['dau-phu', 'khac', 200]]]);
    trayDish('canh-chay', ['tags' => ['loai:canh', 'diet:chay'], 'ingredients' => [['rau-muong', 'rau-cu', 200]]]);
    trayDish('rau-chay', ['tags' => ['diet:chay'], 'ingredients' => [['bi-xanh', 'rau-cu', 200]]]);

    $resp = $this->postJson('/api/shake', ['slot' => 'trua', 'mode' => 'home', 'dish_count' => 3, 'vegetarian' => true]);
    $resp->assertOk()->assertJsonCount(3, 'dishes');
    expect(array_column($resp->json('dishes'), 'slug'))->not->toContain('ca-cham-mam');
});

test('kho đang trống (dữ liệu đang dựng lại) thì báo đang cập nhật, không kêu nới bộ lọc', function () {
    // Không tạo món nào: kho rỗng toàn cục — tình huống gặp khi hệ thống seed lại dữ liệu.
    $home = $this->postJson('/api/shake', ['slot' => 'trua', 'mode' => 'home']);
    $home->assertOk()->assertJsonCount(0, 'dishes');
    expect($home->json('suggestion'))->toContain('đang cập nhật lại dữ liệu');

    $out = $this->postJson('/api/shake', ['slot' => 'trua', 'mode' => 'eat_out']);
    $out->assertOk()->assertJsonPath('dish', null);
    expect($out->json('suggestion'))->toContain('đang cập nhật lại dữ liệu');
});

test('hồ sơ lưu chế độ lắc + số món và lắc tự áp dụng khi không truyền tham số', function () {
    trayFixtures();
    $user = User::factory()->create();
    $user->profile()->create(['shake_mode' => 'home', 'tray_dish_count' => 2]);

    $resp = $this->actingAs($user)->postJson('/api/shake', ['slot' => 'trua']);
    $resp->assertOk()
        ->assertJsonPath('mode', 'home')
        ->assertJsonPath('dish_count', 2)
        ->assertJsonCount(2, 'dishes');

    $put = $this->actingAs($user)->putJson('/api/profile', ['shake_mode' => 'eat_out', 'tray_dish_count' => 4]);
    $put->assertOk()
        ->assertJsonPath('profile.shake_mode', 'eat_out')
        ->assertJsonPath('profile.tray_dish_count', 4);
});
