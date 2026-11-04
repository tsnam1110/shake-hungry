<?php

declare(strict_types=1);

use App\Models\User;

// WEEK-001, Phần 2.4: trang /tuan-an theo ld-*, vào từ Hồ sơ và sidebar.

it('khách thấy trang Tuần ăn với ngày ví dụ, không thấy form tạo tuần', function () {
    $this->get('/tuan-an')
        ->assertOk()
        ->assertSee('Tuần ăn', false)
        ->assertSee('Ngày ví dụ', false)
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
    $this->getJson('/api/week-plans/example')->assertOk()
        ->assertJsonPath('note', 'Ngày ví dụ, không lưu, không cá nhân hoá theo hồ sơ.');
});
