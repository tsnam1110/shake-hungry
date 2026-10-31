<?php

declare(strict_types=1);

use App\Models\User;

/* UI-016: trang hồ sơ cho khách + các trang chính sách. */

it('trang chính sách mở được cho mọi người và có tiêu đề đúng', function (string $url, string $title) {
    $this->get($url)->assertOk()->assertSee($title);
})->with([
    ['/dieu-khoan', 'Điều khoản sử dụng'],
    ['/chinh-sach-bao-mat', 'Chính sách bảo mật'],
    ['/mien-tru-y-te', 'Miễn trừ y tế và dinh dưỡng'],
    ['/nguon-du-lieu', 'Nguồn dữ liệu và giấy phép'],
]);

it('khách chưa đăng nhập thấy giao diện hồ sơ giới thiệu thay vì bị chuyển hướng', function () {
    $this->get('/ho-so')
        ->assertOk()
        ->assertSee('Hồ sơ của bạn')
        ->assertSee('Tạo tài khoản miễn phí')
        ->assertDontSee('Thống kê ăn uống');
});

it('người dùng đã đăng nhập vẫn thấy hồ sơ đầy đủ, không thấy bản khách', function () {
    $this->actingAs(User::factory()->create(['role' => 'user']));
    $this->get('/ho-so')
        ->assertOk()
        ->assertSee('Thống kê ăn uống')
        ->assertDontSee('Tạo tài khoản miễn phí');
});
