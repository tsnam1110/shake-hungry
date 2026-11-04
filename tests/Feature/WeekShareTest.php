<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\WeekPlan;
use App\Models\WeekPlanShare;

// Giai đoạn 4, Task 2: chia sẻ tuần bằng mã chỉ-đọc. Người xem không
// thấy mục tiêu, ngân sách hay thông tin người dùng; mã hết hạn thì 404.

it('tạo mã chia sẻ, xem qua mã chỉ thấy món và đi chợ, không thấy mục tiêu hay ngân sách', function () {
    $user = User::factory()->create(['role' => 'user']);
    $plan = WeekPlan::create(['user_id' => $user->id, 'start_date' => '2026-11-09', 'end_date' => '2026-11-15', 'goal' => 'giam', 'target_protein_g_per_day' => 175, 'target_kcal_per_day' => 2000, 'budget_vnd' => 1500000]);
    $this->actingAs($user);

    $resp = $this->postJson("/api/week-plans/{$plan->id}/shares")->assertCreated();
    $code = $resp->json('code');
    expect($code)->toMatch('/^[A-Z0-9]{4}-[A-Z0-9]{4}$/');

    // Người xem không cần đăng nhập.
    auth()->logout();
    $view = $this->getJson("/api/shares/{$code}")->assertOk();
    $json = $view->json();
    expect(array_key_exists('target_protein_g_per_day', $json['plan']))->toBeFalse()
        ->and(array_key_exists('budget_vnd', $json['plan']))->toBeFalse()
        ->and(array_key_exists('goal', $json['plan']))->toBeFalse()
        ->and($json['plan']['start_date'])->toBe('2026-11-09');

    $this->getJson('/api/shares/XXXX-YYYY')->assertNotFound();
});

it('mã hết hạn hoặc đã thu hồi thì không xem được', function () {
    $user = User::factory()->create(['role' => 'user']);
    $plan = WeekPlan::create(['user_id' => $user->id, 'start_date' => '2026-11-09', 'end_date' => '2026-11-15']);
    $share = WeekPlanShare::create(['week_plan_id' => $plan->id, 'code_hash' => hash('sha256', 'ABCD-1234'), 'expires_at' => now()->subDay()]);
    $this->getJson('/api/shares/ABCD-1234')->assertNotFound();

    $share->update(['expires_at' => now()->addDays(14), 'revoked_at' => now()]);
    $this->getJson('/api/shares/ABCD-1234')->assertNotFound();
});

it('người khác không tạo được mã cho tuần của người khác', function () {
    $owner = User::factory()->create(['role' => 'user']);
    $plan = WeekPlan::create(['user_id' => $owner->id, 'start_date' => '2026-11-09', 'end_date' => '2026-11-15']);
    $other = User::factory()->create(['role' => 'user']);
    $this->actingAs($other);
    $this->postJson("/api/week-plans/{$plan->id}/shares")->assertNotFound();
});
