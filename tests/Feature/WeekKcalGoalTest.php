<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\UserProfile;
use App\Profile\WeekTargetSuggester;
use App\Week\WeekBuilder;

// Tỉ lệ kcal theo goal — CHỐT 2026-11-04 theo đề xuất đã được Benjamin
// duyệt (main chat 21:44, lấy theo công thức Mealbird, ghi rõ là quy tắc
// Lắc Đói chọn, không phải chuẩn y tế phổ quát): giảm −20% / duy trì 0% /
// tăng +10%, làm tròn 50 kcal, áp trên kcal Hồ sơ đã nhập, không suy từ
// TDEE, và không ghi đè kcal Hồ sơ.

it('điều chỉnh kcal theo goal và làm tròn 50', function () {
    $s = new WeekTargetSuggester;
    expect($s->adjustKcalForGoal(2000, 'giam'))->toBe(1600)
        ->and($s->adjustKcalForGoal(2000, 'duy-tri'))->toBe(2000)
        ->and($s->adjustKcalForGoal(2000, 'tang'))->toBe(2200)
        ->and($s->adjustKcalForGoal(2030, 'giam'))->toBe(1600)
        ->and($s->adjustKcalForGoal(2000, null))->toBe(2000)
        ->and($s->adjustKcalForGoal(null, 'giam'))->toBeNull();
});

it('builder snapshot kcal tuần đã điều chỉnh, kcal Hồ sơ giữ nguyên', function () {
    $user = User::factory()->create(['role' => 'user']);
    UserProfile::create(['user_id' => $user->id, 'goal' => 'giam', 'target_protein_g_per_day' => 100, 'target_kcal_per_day' => 2000]);

    $plan = app(WeekBuilder::class)->build($user, '2026-11-09');

    expect($plan->goal)->toBe('giam')
        ->and($plan->target_kcal_per_day)->toBe(1600)
        ->and($plan->target_protein_g_per_day)->toBe(100)
        ->and($user->profile->fresh()->target_kcal_per_day)->toBe(2000);
});
