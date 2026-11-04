<?php

declare(strict_types=1);

use App\Models\User;

// Quy định cocktail, bước 2/4 (2026-11-04): lọc tuổi cho đồ uống có cồn
// trong Hồ sơ — người dùng tự bật, mặc định tắt, không suy từ birth_year.
// Hiện chỉ lưu cờ; Lắc vẫn chỉ gợi ý Dish (bước 1).

it('lọc tuổi đồ có cồn mặc định tắt và chỉ bật khi người dùng tự bật', function () {
    $user = User::factory()->create(['role' => 'user', 'password' => 'secret-pass']);
    $this->actingAs($user);

    $resp = $this->putJson('/api/profile', ['height_cm' => 170])->assertOk();
    expect($resp->json('profile.allow_alcohol'))->toBeFalse();

    $resp = $this->putJson('/api/profile', ['allow_alcohol' => true])->assertOk();
    expect($resp->json('profile.allow_alcohol'))->toBeTrue();

    $this->getJson('/api/profile')->assertOk()
        ->assertJsonPath('profile.allow_alcohol', true);
});

it('năm sinh không tự bật lọc tuổi đồ có cồn', function () {
    // Người sinh năm 1990 đã đủ 18 tuổi, nhưng cờ vẫn phải tắt cho tới
    // khi người dùng tự bật — không suy tuổi từ birth_year.
    $user = User::factory()->create(['role' => 'user', 'password' => 'secret-pass']);
    $this->actingAs($user);

    $resp = $this->putJson('/api/profile', ['birth_year' => 1990])->assertOk();
    expect($resp->json('profile.allow_alcohol'))->toBeFalse();
});
