<?php

declare(strict_types=1);

use App\Models\Dish;
use App\Models\DishName;
use App\Models\Ingredient;

/**
 * Tên gọi khác / tên vùng miền (ALIAS-001): người dùng tìm bằng tên địa phương
 * phải ra đúng món / nguyên liệu, và API chi tiết phải trả kèm alias + nhãn vùng.
 */
function aliasDish(): Dish
{
    $dish = Dish::create([
        'slug' => 'canh-muop-dang-nhoi-thit',
        'country_code' => 'VN',
        'cooking_method' => 'nau',
        'completeness_level' => 1,
        'is_eat_out' => true,
        'is_home_cook' => true,
        'source' => 'curated',
        'confidence' => 'medium',
    ]);
    DishName::create(['dish_id' => $dish->id, 'locale' => 'vi', 'name' => 'Canh mướp đắng nhồi thịt', 'is_primary' => true, 'is_alias' => false]);
    DishName::create(['dish_id' => $dish->id, 'locale' => 'vi', 'name' => 'Canh khổ qua nhồi thịt', 'is_primary' => false, 'is_alias' => true, 'note' => 'miền Nam']);

    return $dish;
}

test('tìm món theo tên gọi khác (tên vùng miền) qua API danh mục', function () {
    aliasDish();

    $this->getJson('/api/dishes?q='.urlencode('khổ qua'))
        ->assertOk()
        ->assertJsonPath('data.0.slug', 'canh-muop-dang-nhoi-thit');
});

test('chi tiết món trả kèm alias và nhãn vùng miền', function () {
    aliasDish();

    $response = $this->getJson('/api/dishes/canh-muop-dang-nhoi-thit')->assertOk();
    $aliases = collect($response->json('dish.names'))->where('is_alias', true)->values();
    expect($aliases)->toHaveCount(1)
        ->and($aliases[0]['name'])->toBe('Canh khổ qua nhồi thịt')
        ->and($aliases[0]['note'])->toBe('miền Nam');
});

test('tìm nguyên liệu theo tên gọi khác qua API công khai', function () {
    $ingredient = Ingredient::create([
        'slug' => 'dua-thom',
        'name_vi' => 'Dứa thơm',
        'category' => 'trai-cay',
        'mapping_confidence' => 'high',
        'source' => 'curated',
        'confidence' => 'high',
    ]);
    $ingredient->aliases()->create(['name' => 'Khóm', 'note' => 'miền Nam']);
    $ingredient->aliases()->create(['name' => 'Thơm', 'note' => 'miền Trung, miền Nam']);

    $this->getJson('/api/ingredients?q='.urlencode('Khóm'))
        ->assertOk()
        ->assertJsonPath('data.0.slug', 'dua-thom')
        ->assertJsonPath('data.0.aliases.0.name', 'Khóm');
});
