<?php

declare(strict_types=1);

use App\Models\Dish;

/*
 * Hiển thị cho người dùng Việt (DATA-009 bổ sung): mã cách chế biến lưu dạng
 * slug (nau, luoc…) không bao giờ được lộ ra giao diện — API chi tiết món
 * phải kèm nhãn có dấu cooking_method_label.
 */

function displayDish(array $overrides = []): Dish
{
    return Dish::create(array_merge([
        'slug' => 'mon-hien-thi-'.uniqid(),
        'country_code' => 'VN',
        'cooking_method' => 'luoc',
        'completeness_level' => 1,
        'is_eat_out' => true,
        'is_home_cook' => true,
        'source' => 'curated',
        'confidence' => 'medium',
        'needs_review' => true,
    ], $overrides));
}

test('API chi tiết món trả nhãn cách làm có dấu thay cho mã slug', function () {
    $dish = displayDish(['cooking_method' => 'luoc']);
    $dish->names()->create(['locale' => 'vi', 'name' => 'Rau luộc', 'is_primary' => true, 'is_alias' => false]);

    $this->getJson('/api/dishes/'.$dish->slug)
        ->assertOk()
        ->assertJsonPath('dish.cooking_method', 'luoc')
        ->assertJsonPath('dish.cooking_method_label', 'Luộc');

    $fried = displayDish(['cooking_method' => 'chien-ngap-dau']);
    $this->getJson('/api/dishes/'.$fried->slug)
        ->assertOk()
        ->assertJsonPath('dish.cooking_method_label', 'Chiên ngập dầu');
});

test('món không có cách chế biến thì nhãn là null', function () {
    $dish = displayDish(['cooking_method' => null]);

    $this->getJson('/api/dishes/'.$dish->slug)
        ->assertOk()
        ->assertJsonPath('dish.cooking_method_label', null);
});
