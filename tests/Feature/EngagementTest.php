<?php

declare(strict_types=1);

use App\Models\Dish;
use App\Models\Fruit;
use App\Models\Ingredient;
use App\Models\User;

/* UI-014: yêu thích món/quả, ghi ăn quả, thống kê số lượng đã ăn. */

function engUser(): User
{
    return User::factory()->create(['role' => 'user']);
}

function engDish(string $slug): Dish
{
    $dish = Dish::create(['slug' => $slug, 'country_code' => 'VN', 'source' => 'curated', 'confidence' => 'low', 'completeness_level' => 1]);
    $dish->names()->create(['locale' => 'vi', 'name' => $slug, 'is_primary' => true, 'is_alias' => false]);

    return $dish;
}

function engFruit(string $slug): Fruit
{
    $ing = Ingredient::create(['name_vi' => $slug, 'slug' => $slug, 'category' => 'trai-cay', 'mapping_confidence' => 'high', 'source' => 'curated', 'confidence' => 'high']);

    return Fruit::create(['ingredient_id' => $ing->id, 'slug' => $slug, 'classification_group' => 'khac', 'origin' => 'trong-nuoc', 'availability_months' => [1, 2, 3], 'availability_type' => 'theo-mua']);
}

it('yêu thích: bật/tắt món và quả, khách không dùng được', function () {
    $dish = engDish('mon-yeu-thich');
    $fruit = engFruit('qua-yeu-thich');

    $this->postJson('/api/favorites/toggle', ['dish_id' => $dish->id])->assertUnauthorized();

    $this->actingAs(engUser());
    $this->postJson('/api/favorites/toggle', ['dish_id' => $dish->id])->assertCreated()->assertJsonPath('favored', true);
    $this->postJson('/api/favorites/toggle', ['fruit_id' => $fruit->id])->assertCreated()->assertJsonPath('favored', true);
    $this->getJson('/api/favorites/ids')
        ->assertJsonPath('dish_ids', [$dish->id])
        ->assertJsonPath('fruit_ids', [$fruit->id]);

    $this->postJson('/api/favorites/toggle', ['dish_id' => $dish->id])->assertOk()->assertJsonPath('favored', false);
    $this->getJson('/api/favorites')->assertJsonCount(0, 'dishes')->assertJsonCount(1, 'fruits');
    $this->postJson('/api/favorites/toggle', ['dish_id' => $dish->id, 'fruit_id' => $fruit->id])->assertUnprocessable();
});

it('thống kê: đếm số lượng món đã ăn và quả đã ăn theo từng loại', function () {
    $user = engUser();
    $this->actingAs($user);
    $a = engDish('mon-a');
    $b = engDish('mon-b');
    foreach ([$a, $a, $b] as $d) {
        $this->postJson('/api/meal-logs', ['dish_id' => $d->id, 'slot' => 'trua', 'source' => 'manual'])->assertCreated();
    }
    $f = engFruit('qua-a');
    $this->postJson('/api/fruit-logs', ['fruit_id' => $f->id])->assertCreated();
    $this->postJson('/api/fruit-logs', ['fruit_id' => $f->id])->assertCreated();

    $this->getJson('/api/stats')
        ->assertJsonPath('meals_total', 3)
        ->assertJsonPath('dishes_distinct', 2)
        ->assertJsonPath('top_dishes.0.count', 2)
        ->assertJsonPath('fruits_total', 2)
        ->assertJsonPath('fruits_distinct', 1)
        ->assertJsonPath('top_fruits.0.count', 2);
});

it('nhật ký: fruit-logs theo ngày + xoá của mình, meal-logs theo khoảng ngày (UI-015)', function () {
    $user = engUser();
    $this->actingAs($user);
    $f = engFruit('qua-nhat-ky');
    $logId = $this->postJson('/api/fruit-logs', ['fruit_id' => $f->id, 'eaten_at' => '2026-10-30T12:00:00'])->assertCreated()->json('id');
    $this->postJson('/api/fruit-logs', ['fruit_id' => $f->id, 'eaten_at' => '2026-10-29T12:00:00'])->assertCreated();

    $this->getJson('/api/fruit-logs?date=2026-10-30')
        ->assertJsonCount(1, 'logs')
        ->assertJsonPath('logs.0.name', 'qua-nhat-ky');
    $this->deleteJson("/api/fruit-logs/{$logId}")->assertOk();
    $this->getJson('/api/fruit-logs?date=2026-10-30')->assertJsonCount(0, 'logs');

    $dish = engDish('mon-nhat-ky');
    $this->postJson('/api/meal-logs', ['dish_id' => $dish->id, 'slot' => 'trua', 'source' => 'manual', 'eaten_at' => '2026-10-30T12:00:00'])->assertCreated();
    $this->postJson('/api/meal-logs', ['dish_id' => $dish->id, 'slot' => 'toi', 'source' => 'manual', 'eaten_at' => '2026-10-31T19:00:00'])->assertCreated();
    $days = $this->getJson('/api/meal-logs?from=2026-10-29&to=2026-10-31')->assertOk()->json('days');
    expect($days)->toHaveKeys(['2026-10-29', '2026-10-30', '2026-10-31']);
});
