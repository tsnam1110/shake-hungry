<?php

declare(strict_types=1);

use App\Models\Dish;
use App\Models\Drink;
use App\Models\Ingredient;
use App\Models\User;
use App\Recommendation\DrinkRecommender;
use App\Recommendation\Recommender;

function companionDrink(string $slug, array $overrides = []): Drink
{
    return Drink::create(array_replace([
        'slug' => $slug, 'name_vi' => $slug, 'kind' => 'pha-che',
        'drink_group' => 'giai-khat', 'alcohol' => false, 'needs_review' => false,
        'uncertain' => false, 'difficulty' => 'dễ', 'prep_minutes' => 5,
        'technique' => 'khuấy', 'garnish' => [],
        'ingredients' => [['name' => 'nước lọc', 'ingredient_slug' => 'nuoc-loc']],
        'recipe_steps' => ['Khuấy đều.'],
    ], $overrides));
}

function companionIngredient(string $slug, string $category = 'khac'): Ingredient
{
    return Ingredient::firstOrCreate(['slug' => $slug], [
        'name_vi' => $slug, 'category' => $category,
        'mapping_confidence' => 'high', 'source' => 'curated', 'confidence' => 'high',
    ]);
}

function companionMealMock(): void
{
    $dish = Dish::create([
        'slug' => 'mon-kem-nuoc', 'country_code' => 'VN', 'completeness_level' => 1,
        'status' => 'draft', 'source' => 'curated', 'confidence' => 'low',
        'is_home_cook' => true, 'is_eat_out' => true,
    ]);
    $dish->names()->create(['locale' => 'vi', 'name' => 'Món kiểm thử', 'is_primary' => true]);
    $engine = Mockery::mock(Recommender::class);
    $engine->shouldReceive('shake')->andReturn([
        'dish' => $dish, 'seed' => 'drink-test-seed', 'pool_size' => 1, 'relaxed_level' => 0,
    ]);
    $engine->shouldReceive('shakeTray')->andReturn([
        'dishes' => [['dish' => $dish, 'role' => 'chinh']],
        'seed' => 'drink-test-seed', 'pool_size' => 1, 'relaxed_level' => 0,
    ]);
    app()->instance(Recommender::class, $engine);
}

it('nước kèm là opt-in, không thay hợp đồng phản hồi khi không chọn', function () {
    companionMealMock();
    companionDrink('nuoc-kiem-thu');
    $response = $this->postJson('/api/shake', ['slot' => 'trua']);
    $response->assertOk()->assertJsonPath('dish.slug', 'mon-kem-nuoc');
    expect($response->json())->not->toHaveKey('drink');
});

it('API kèm nước ở cả ăn ngoài và cơm nhà, không thêm nước vào dishes hoặc kcal', function (string $mode) {
    companionMealMock();
    companionDrink('nuoc-kiem-thu');
    $response = $this->postJson('/api/shake', ['slot' => 'trua', 'mode' => $mode, 'dish_count' => 1, 'include_drink' => true]);
    $response->assertOk()->assertJsonPath('drink.slug', 'nuoc-kiem-thu')
        ->assertJsonPath('drink.alcohol', false)->assertJsonCount(1, 'dishes');
    expect($response->json('drink'))->not->toHaveKey('nutrition');
    if ($mode === 'home') {
        $response->assertJsonPath('tray_kcal', null)->assertJsonPath('dish_count', 1);
    }
})->with(['eat_out', 'home']);

it('cơm nhà chỉ chọn công thức dễ pha, không đóng gói/quầy bar/máy ép mía', function () {
    foreach ([
        ['kind' => 'dong-goi'], ['drink_group' => 'ca-phe'], ['drink_group' => 'mocktail'],
        ['difficulty' => 'khó'], ['prep_minutes' => 40], ['prep_minutes' => null],
        ['recipe_steps' => []], ['ingredients' => []], ['technique' => 'ép'],
    ] as $index => $overrides) {
        companionDrink('khong-pha-nha-'.$index, $overrides);
    }
    $eligible = companionDrink('nuoc-chanh-test');
    expect(app(DrinkRecommender::class)->pick([], 'home', 'seed')?->id)->toBe($eligible->id);
});

it('không gợi ý cồn, bia không cồn, cocktail, mocktail hoặc hồ sơ chưa duyệt', function () {
    foreach ([
        ['alcohol' => true], ['alcohol_pct_estimate' => 1], ['alcohol_pct_range' => ['min' => 1, 'max' => 2]],
        ['drink_group' => 'cocktail-co-con'], ['drink_group' => 'mocktail'], ['drink_group' => 'bia'],
        ['needs_review' => true],
    ] as $index => $overrides) {
        companionDrink('bi-loai-'.$index, $overrides);
    }
    expect(app(DrinkRecommender::class)->pick([], 'eat_out', 'seed'))->toBeNull();
});

it('giữ nguyên nhãn chưa chắc chắn thay vì bịa số hoặc bỏ ghi chú của nước đã duyệt', function () {
    companionMealMock();
    companionDrink('cam-co-luu-y', ['uncertain' => true, 'uncertain_note' => 'Một chỉ số đang chờ xác minh.']);
    $this->postJson('/api/shake', ['slot' => 'trua', 'include_drink' => true])
        ->assertOk()->assertJsonPath('drink.uncertain', true)
        ->assertJsonPath('drink.uncertain_note', 'Một chỉ số đang chờ xác minh.');
});

it('dị ứng loại cả nguyên liệu, cùng nhóm và thành phần con, không nới khi hết nước', function () {
    $milk = companionIngredient('sua-tuoi');
    $condensed = companionIngredient('sua-dac');
    $composite = companionIngredient('hon-hop-kiem-thu');
    $composite->components()->attach($condensed);
    companionDrink('co-sua', ['ingredients' => [['ingredient_slug' => $condensed->slug]]]);
    companionDrink('co-sua-an', ['ingredients' => [['ingredient_slug' => $composite->slug]]]);
    companionDrink('thieu-anh-xa', ['ingredients' => [['name' => 'thành phần chưa ánh xạ']]]);
    companionDrink('thieu-ho-so', ['ingredients' => [['ingredient_slug' => 'khong-ton-tai']]]);
    companionDrink('trang-tri-chua-ro', ['garnish' => ['topping chưa ánh xạ']]);
    companionDrink('dong-goi', ['kind' => 'dong-goi']);
    expect(app(DrinkRecommender::class)->pick(['allergens' => [$milk->slug]], 'home', 'seed'))->toBeNull();
    companionIngredient('nuoc-loc');
    companionDrink('nuoc-loc-test');
    expect(app(DrinkRecommender::class)->pick(['allergens' => [$milk->slug]], 'home', 'seed')?->slug)->toBe('nuoc-loc-test');
});

it('ăn chay loại thành phần động vật và công thức chưa rõ, không ảnh hưởng nước thực vật', function () {
    companionIngredient('nuoc-loc');
    companionIngredient('gelatin');
    companionDrink('gelatin-test', ['ingredients' => [['ingredient_slug' => 'gelatin']]]);
    companionDrink('chua-ro-test', ['ingredients' => [['name' => 'chưa ánh xạ']]]);
    companionDrink('nuoc-loc-test');
    expect(app(DrinkRecommender::class)->pick(['vegetarian' => true], 'home', 'seed')?->slug)->toBe('nuoc-loc-test');
});

it('dị ứng từ hồ sơ vẫn áp dụng khi request không gửi lại', function () {
    companionMealMock();
    $user = User::factory()->create();
    $user->profile()->create(['excluded_ingredients' => ['sua-tuoi']]);
    companionIngredient('sua-tuoi');
    companionDrink('sua-test', ['ingredients' => [['ingredient_slug' => 'sua-tuoi']]]);
    $this->actingAs($user)->postJson('/api/shake', ['slot' => 'trua', 'include_drink' => true])
        ->assertOk()->assertJsonPath('drink', null);
});

it('hết nước chỉ báo thiếu nước, không loại món ăn hợp lệ', function () {
    companionMealMock();
    $this->postJson('/api/shake', ['slot' => 'trua', 'mode' => 'home', 'include_drink' => true])
        ->assertOk()->assertJsonPath('dish.slug', 'mon-kem-nuoc')->assertJsonPath('drink', null)
        ->assertJsonPath('drink_suggestion', fn ($text) => is_string($text) && $text !== '');
});

it('lựa chọn nước tái lập theo seed và không làm thay đổi dữ liệu kho', function () {
    companionDrink('nuoc-a');
    companionDrink('nuoc-b');
    $service = app(DrinkRecommender::class);
    expect($service->pick([], 'home', 'same-seed')?->slug)->toBe($service->pick([], 'home', 'same-seed')?->slug)
        ->and(Drink::count())->toBe(2);
});
