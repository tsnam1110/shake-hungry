<?php

declare(strict_types=1);

use App\Models\Dish;
use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/* GĐ5: USR-001 tài khoản, USR-002 TDEE, USR-003 nhật ký, USR-004 cá nhân hoá. */

function endUser(array $overrides = []): User
{
    return User::factory()->create(['role' => 'user', 'password' => 'secret-pass', ...$overrides]);
}

it('đăng ký tạo tài khoản role user và user không vào được khu vực quản trị', function () {
    $this->postJson('/api/register', [
        'name' => 'Người Dùng', 'email' => 'user@example.com',
        'password' => 'secret-pass', 'password_confirmation' => 'secret-pass',
    ])->assertCreated()->assertJsonPath('user.role', 'user');

    $this->getJson('/admin/api/me')->assertForbidden();
});

it('đăng nhập sai mật khẩu bị từ chối, quên mật khẩu luôn trả ok', function () {
    $user = endUser();
    $this->postJson('/api/login', ['email' => $user->email, 'password' => 'sai'])->assertUnprocessable();
    $this->postJson('/api/forgot-password', ['email' => 'khong-ton-tai@example.com'])->assertOk();
});

it('TDEE dùng Mifflin-St Jeor và hiển thị khoảng PAL của FAO', function () {
    $user = endUser();
    $this->actingAs($user);
    // Nam, sinh 1990 (36 tuổi năm 2026), 70kg, 170cm → BMR = 700 + 1062,5 − 180 + 5 = 1587,5
    $resp = $this->putJson('/api/profile', [
        'height_cm' => 170, 'weight_kg' => 70, 'birth_year' => 1990,
        'sex' => 'male', 'activity_level' => 'nhe',
    ])->assertOk();
    $tdee = $resp->json('tdee');
    expect($tdee['formula'])->toBe('Mifflin–St Jeor')
        ->and($tdee['bmr'])->toEqual(1588)
        ->and($tdee['tdee_min'])->toBe(2220)   // 1587,5 × 1,40 làm tròn chục
        ->and($tdee['tdee_max'])->toBe(2680);  // 1587,5 × 1,69 làm tròn chục
});

it('thiếu chiều cao thì TDEE chuyển sang Schofield', function () {
    $this->actingAs(endUser());
    $resp = $this->putJson('/api/profile', [
        'weight_kg' => 60, 'birth_year' => 2000, 'sex' => 'female', 'activity_level' => 'vua',
    ])->assertOk();
    expect($resp->json('tdee.formula'))->toStartWith('Schofield');
});

it('nhật ký bữa ăn: ghi hai nguồn, tổng khoảng bỏ qua món chưa đủ dữ liệu và đếm rõ', function () {
    $user = endUser();
    $this->actingAs($user);
    $withData = Dish::create(['slug' => 'mon-co-so', 'country_code' => 'VN', 'source' => 'curated', 'confidence' => 'low', 'completeness_level' => 1]);
    DB::table('dish_nutrition')->insert([
        'dish_id' => $withData->id, 'basis' => 'per_serving', 'kcal_min' => 400, 'kcal_max' => 600,
        'confidence' => 'medium', 'fct_source' => 'usda_sr_legacy', 'missing_ingredients' => '[]',
        'computed_at' => now(), 'engine_version' => '1.1.0', 'source' => 'computed',
    ]);
    $noData = Dish::create(['slug' => 'mon-chua-co-so', 'country_code' => 'VN', 'source' => 'curated', 'confidence' => 'low', 'completeness_level' => 1]);

    $this->postJson('/api/meal-logs', ['dish_id' => $withData->id, 'slot' => 'trua', 'source' => 'shake_accepted'])->assertCreated();
    $this->postJson('/api/meal-logs', ['dish_id' => $noData->id, 'slot' => 'toi', 'source' => 'manual'])->assertCreated();

    $resp = $this->getJson('/api/meal-logs?date='.now()->toDateString())->assertOk();
    expect($resp->json('summary.kcal_min'))->toBe(400)
        ->and($resp->json('summary.kcal_max'))->toBe(600)
        ->and($resp->json('summary.dishes_unknown'))->toBe(1);

    $logId = $resp->json('logs.0.id');
    $this->deleteJson("/api/meal-logs/{$logId}")->assertOk();
    $this->assertDatabaseCount('meal_logs', 1);
});

it('không sửa/xoá được nhật ký của người khác', function () {
    $owner = endUser();
    $dish = Dish::create(['slug' => 'mon-cua-nguoi-khac', 'country_code' => 'VN', 'source' => 'curated', 'confidence' => 'low', 'completeness_level' => 1]);
    $log = $owner->mealLogs()->create(['dish_id' => $dish->id, 'slot' => 'trua', 'source' => 'manual', 'eaten_at' => now()]);
    $this->actingAs(endUser());
    $this->deleteJson("/api/meal-logs/{$log->id}")->assertNotFound();
});

it('lắc cá nhân hoá: dị ứng trong hồ sơ là lọc cứng và món vừa ăn bị loại', function () {
    $user = endUser();
    $this->actingAs($user);
    $this->putJson('/api/profile', ['excluded_ingredients' => ['lac-rang']])->assertOk();

    $mk = function (string $slug, ?string $ingSlug) {
        $dish = Dish::create(['slug' => $slug, 'country_code' => 'VN', 'source' => 'curated', 'confidence' => 'low', 'completeness_level' => 1, 'serving_grams' => 300]);
        $dish->names()->create(['locale' => 'vi', 'name' => $slug, 'is_primary' => true, 'is_alias' => false]);
        $dish->mealSlots()->create(['slot' => 'trua', 'weight' => 8]);
        if ($ingSlug !== null) {
            $ing = Ingredient::create(['name_vi' => $ingSlug, 'slug' => $ingSlug, 'category' => 'khac', 'mapping_confidence' => 'high', 'source' => 'curated', 'confidence' => 'high']);
            $dish->dishIngredients()->create(['ingredient_id' => $ing->id, 'quantity' => 50, 'unit_name' => 'g', 'source' => 'curated']);
        }
        DB::table('dish_nutrition')->insert([
            'dish_id' => $dish->id, 'basis' => 'per_serving', 'kcal_min' => 300, 'kcal_max' => 500,
            'confidence' => 'medium', 'fct_source' => 'usda_sr_legacy', 'missing_ingredients' => '[]',
            'computed_at' => now(), 'engine_version' => '1.1.0', 'source' => 'computed',
        ]);

        return $dish;
    };
    $mk('mon-co-lac', 'lac-rang');
    $safe = $mk('mon-an-toan', null);
    $eaten = $mk('mon-vua-an', null);
    $user->mealLogs()->create(['dish_id' => $eaten->id, 'slot' => 'sang', 'source' => 'manual', 'eaten_at' => now()]);

    $resp = $this->postJson('/api/shake', ['slot' => 'trua', 'shake_index' => 0])->assertOk();
    expect($resp->json('dish.id'))->toBe($safe->id);
});

it('hồ sơ lưu tuỳ chọn lắc: vùng miền yêu thích + kcal mục tiêu mỗi bữa (UI-011)', function () {
    $user = endUser();
    $this->actingAs($user);
    $regionId = \App\Models\Region::create(['country_code' => 'VN', 'code' => 'vn-test', 'name' => 'Miền Test', 'level' => 'macro_region'])->id;

    $this->putJson('/api/profile', [
        'preferred_region_id' => $regionId,
        'target_kcal_per_meal' => 650,
    ])->assertOk()
        ->assertJsonPath('profile.preferred_region_id', $regionId)
        ->assertJsonPath('profile.target_kcal_per_meal', 650);

    // Cập nhật từng phần (trang chủ lưu ngược từng tuỳ chọn) không xoá trường còn lại
    $this->putJson('/api/profile', ['target_kcal_per_meal' => 700])->assertOk();
    $this->getJson('/api/profile')
        ->assertJsonPath('profile.preferred_region_id', $regionId)
        ->assertJsonPath('profile.target_kcal_per_meal', 700);

    $this->putJson('/api/profile', ['target_kcal_per_meal' => 50])->assertUnprocessable();
    $this->putJson('/api/profile', ['preferred_region_id' => 999999])->assertUnprocessable();
});
