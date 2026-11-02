<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\UserProfile;

/* TCM-003 (2026-11-02): tuỳ chọn hiển thị tính vị của người dùng — mặc định
   bật (quyết định chủ dự án 2026-11-01), tắt được trong hồ sơ; lớp tính vị
   là hiển thị thuần, tuỳ chọn này không được đụng vào engine gợi ý. */

it('show_tcm mặc định bật và tắt/bật lại được qua API hồ sơ', function () {
    $user = User::factory()->create(['role' => 'user']);

    $this->actingAs($user)->putJson('/api/profile', ['show_tcm' => false])
        ->assertOk()
        ->assertJsonPath('profile.show_tcm', false);
    expect(UserProfile::where('user_id', $user->id)->value('show_tcm'))->toBeFalse();

    $this->actingAs($user)->putJson('/api/profile', ['show_tcm' => true])
        ->assertOk()
        ->assertJsonPath('profile.show_tcm', true);
    expect(UserProfile::where('user_id', $user->id)->value('show_tcm'))->toBeTrue();
});

it('trang công khai nhúng ldShowTcm theo tuỳ chọn của người dùng', function () {
    // Khách chưa đăng nhập: mặc định bật.
    $this->get('/')->assertOk()->assertSee('window.ldShowTcm = true', false);

    // Người dùng chưa có hồ sơ: mặc định bật.
    $fresh = User::factory()->create(['role' => 'user']);
    $this->actingAs($fresh)->get('/')->assertOk()->assertSee('window.ldShowTcm = true', false);

    // Người dùng đã tắt trong hồ sơ: nhúng false.
    $off = User::factory()->create(['role' => 'user']);
    UserProfile::create(['user_id' => $off->id, 'show_tcm' => false]);
    $this->actingAs($off)->get('/')->assertOk()->assertSee('window.ldShowTcm = false', false);
});
