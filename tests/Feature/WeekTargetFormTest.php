<?php

declare(strict_types=1);

use App\Models\User;

// WEEK-001, Phần 2.1, Task 3: form Hồ sơ cho mục tiêu tuần.
// Form chỉ là giao diện nhập + hiển thị gợi ý từ API Task 2; không đổi
// schema, không tự suy kcal từ goal, không ghi đè số đã nhập.

it('người dùng đã đăng nhập thấy form mục tiêu tuần trong trang Hồ sơ', function () {
    $this->actingAs(User::factory()->create(['role' => 'user']));

    $this->get('/ho-so')
        ->assertOk()
        ->assertSee('Mục tiêu tuần ăn', false)
        ->assertSee('name="goal"', false)
        ->assertSee('name="target_protein_g_per_day"', false)
        ->assertSee('name="target_kcal_per_day"', false)
        ->assertSee('id="week-target-suggestion"', false);
});

it('khách chưa đăng nhập không thấy form mục tiêu tuần', function () {
    $this->get('/ho-so')
        ->assertOk()
        ->assertDontSee('name="goal"', false)
        ->assertDontSee('name="target_protein_g_per_day"', false)
        ->assertDontSee('name="target_kcal_per_day"', false);
});
