<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\WeekPlan;

// Giai đoạn 4, Task 1: ngân sách mềm — chỉ là mục tiêu, không kết luận
// dưới/vượt vì chưa có tổng tiền thật.

it('đặt ngân sách mềm cho tuần, chỉ của chính người dùng, và ghi rõ chưa có tổng tiền thật', function () {
    $user = User::factory()->create(['role' => 'user']);
    $plan = WeekPlan::create(['user_id' => $user->id, 'start_date' => '2026-11-09', 'end_date' => '2026-11-15']);
    $this->actingAs($user);

    $resp = $this->postJson("/api/week-plans/{$plan->id}/budget", ['budget_vnd' => 1500000])->assertOk();
    expect($resp->json('plan.budget_vnd'))->toBe(1500000)
        ->and($resp->json('note'))->toContain('Chưa tính được tổng tiền thật');

    $plan->refresh();
    expect($plan->budget_vnd)->toBe(1500000);

    $this->postJson("/api/week-plans/{$plan->id}/budget", ['budget_vnd' => -1])->assertUnprocessable();
});
