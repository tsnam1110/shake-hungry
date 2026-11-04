<?php

declare(strict_types=1);

use App\Models\Dish;
use App\Models\DishNutrition;
use App\Models\User;

// WEEK-001, Phần 2.4: trang /tuan-an theo ld-*, vào từ Hồ sơ và sidebar.
// Tiếp tục 2026-11-04: tuần ví dụ cho khách đủ 7 ngày (trước đó chỉ 1 ngày).

function weekPageDish(string $slug, string $slot): Dish
{
    $dish = Dish::create(['slug' => $slug, 'country_code' => 'VN', 'source' => 'curated', 'confidence' => 'low', 'completeness_level' => 1]);
    $dish->names()->create(['locale' => 'vi', 'name' => $slug, 'is_primary' => true, 'is_alias' => false]);
    $dish->mealSlots()->create(['slot' => $slot, 'weight' => 8]);
    DishNutrition::create(['dish_id' => $dish->id, 'basis' => 'per_serving', 'confidence' => 'medium', 'fct_source' => 'usda_sr_legacy', 'computed_at' => now(), 'engine_version' => '1.1.0', 'source' => 'computed', 'kcal_min' => 400, 'kcal_max' => 500, 'protein_g' => 20, 'missing_ingredients' => [], 'unverified_ingredients' => []]);

    return $dish;
}

it('khách thấy trang Tuần ăn với tuần ví dụ, không thấy form tạo tuần', function () {
    $this->get('/tuan-an')
        ->assertOk()
        ->assertSee('Tuần ăn', false)
        ->assertSee('Tuần ví dụ', false)
        ->assertDontSee('Tạo tuần mới', false);
});

it('người dùng đã đăng nhập thấy form tạo tuần', function () {
    $this->actingAs(User::factory()->create(['role' => 'user']));
    $this->get('/tuan-an')
        ->assertOk()
        ->assertSee('Tạo tuần mới', false)
        ->assertSee('Các tuần của bạn', false);
});

it('API tuần ví dụ công khai, không cần đăng nhập, không lưu gì', function () {
    foreach (['sang', 'trua', 'toi', 'an-vat'] as $slot) {
        weekPageDish('wp-' . $slot, $slot);
    }

    $this->getJson('/api/week-plans/example')->assertOk()
        ->assertJsonPath('note', 'Tuần ví dụ 7 ngày, không lưu, không cá nhân hoá theo hồ sơ.')
        ->assertJsonCount(7, 'days')
        ->assertJsonCount(4, 'days.0.meals');
});
