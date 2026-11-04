<?php

declare(strict_types=1);

use App\Models\Dish;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\WeekPlan;
use App\Models\WeekPlanDay;
use App\Models\WeekPlanMeal;

// WEEK-001, Phần 2.2: mô hình tuần. Chỉ kiểm tra quan hệ + snapshot;
// builder/validator ở Phần 2.3 mới tính tổng ngày từ món.

it('tuần lưu snapshot mục tiêu và quan hệ ngày/bữa, đổi hồ sơ sau đó không sửa tuần cũ', function () {
    $user = User::factory()->create(['role' => 'user']);
    UserProfile::create([
        'user_id' => $user->id, 'goal' => 'giam',
        'target_protein_g_per_day' => 175, 'target_kcal_per_day' => 2000,
    ]);
    $dish = Dish::create(['slug' => 'mon-tuan-mau', 'country_code' => 'VN', 'source' => 'curated', 'confidence' => 'low', 'completeness_level' => 1]);

    $plan = WeekPlan::create([
        'user_id' => $user->id, 'start_date' => '2026-11-09', 'end_date' => '2026-11-15',
        'goal' => 'giam', 'target_protein_g_per_day' => 175, 'target_kcal_per_day' => 2000,
    ]);
    $day = WeekPlanDay::create(['week_plan_id' => $plan->id, 'date' => '2026-11-09', 'status' => 'chua-du-du-lieu']);
    WeekPlanMeal::create(['week_plan_day_id' => $day->id, 'dish_id' => $dish->id, 'slot' => 'trua']);

    // Đổi hồ sơ sau khi tạo tuần: snapshot tuần phải giữ nguyên.
    UserProfile::where('user_id', $user->id)->update(['target_protein_g_per_day' => 100]);

    $plan->refresh();
    expect($plan->target_protein_g_per_day)->toBe(175)
        ->and($plan->days)->toHaveCount(1)
        ->and($plan->days->first()->meals)->toHaveCount(1)
        ->and($plan->days->first()->meals->first()->dish->slug)->toBe('mon-tuan-mau')
        ->and($plan->meals)->toHaveCount(1);
});
