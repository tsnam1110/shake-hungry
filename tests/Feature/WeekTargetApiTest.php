<?php

declare(strict_types=1);

use App\Models\User;

// WEEK-001, Phần 2.1, Task 2: API Hồ sơ cho mục tiêu tuần.
// Hàm riêng trong file này để test chạy được cả khi chạy lẻ một file
// (hàm endUser() của UserAccountsTest chỉ nạp khi chạy cả thư mục).

function weekUser(array $overrides = []): User
{
    return User::factory()->create(['role' => 'user', 'password' => 'secret-pass', ...$overrides]);
}

it('lưu mục tiêu tuần và trả gợi ý protein theo kcal đã nhập', function () {
    $user = weekUser();
    $this->actingAs($user);

    $resp = $this->putJson('/api/profile', [
        'height_cm' => 180, 'goal' => 'giam',
        'target_kcal_per_day' => 2000, 'target_protein_g_per_day' => 150,
    ])->assertOk();

    // Gợi ý tính từ Task 1: cao 180 cm → thô 179 g, giới hạn 175 g → gợi ý 175 g.
    // Số người dùng nhập (150 g) được lưu nguyên, không bị gợi ý ghi đè.
    expect($resp->json('profile.goal'))->toBe('giam')
        ->and($resp->json('profile.target_kcal_per_day'))->toBe(2000)
        ->and($resp->json('profile.target_protein_g_per_day'))->toBe(150)
        ->and($resp->json('week_target_suggestion.suggested_g'))->toBe(175)
        ->and($resp->json('week_target_suggestion.protein_raw_g'))->toBe(179)
        ->and($resp->json('week_target_suggestion.protein_cap_g'))->toBe(175);

    $this->getJson('/api/profile')->assertOk()
        ->assertJsonPath('week_target_suggestion.suggested_g', 175);
});

it('từ chối mục tiêu tuần ngoài giới hạn', function () {
    $user = weekUser();
    $this->actingAs($user);

    $this->putJson('/api/profile', ['goal' => 'giam-cuc-manh'])->assertUnprocessable();
    $this->putJson('/api/profile', ['target_protein_g_per_day' => 10])->assertUnprocessable();
    $this->putJson('/api/profile', ['target_kcal_per_day' => 500])->assertUnprocessable();
});

it('chưa nhập kcal mục tiêu thì gợi ý là null, không tự suy số', function () {
    $user = weekUser();
    $this->actingAs($user);

    $resp = $this->putJson('/api/profile', [
        'height_cm' => 180, 'goal' => 'duy-tri',
    ])->assertOk();

    expect($resp->json('week_target_suggestion'))->toBeNull();
});
