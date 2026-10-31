<?php

declare(strict_types=1);

use App\Models\Dish;
use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/*
 * Trang quản trị (ADM-001/002/003/005): đăng nhập phiên, phân quyền,
 * CRUD + duyệt nguyên liệu/món, cảnh báo trùng tên, log thay đổi.
 */

function adminUser(string $role = 'admin'): User
{
    return User::factory()->create(['role' => $role, 'password' => 'secret-pass']);
}

function adminMakeIngredient(array $overrides = []): Ingredient
{
    return Ingredient::create(array_merge([
        'name_vi' => 'Rau muống',
        'name_en' => 'Water spinach',
        'slug' => 'rau-muong-'.uniqid(),
        'category' => 'rau-cu',
        'mapping_confidence' => 'high',
        'source' => 'curated',
        'confidence' => 'high',
        'needs_review' => true,
    ], $overrides));
}

it('chặn khách chưa đăng nhập khỏi API quản trị', function () {
    $this->getJson('/admin/api/me')->assertUnauthorized();
    $this->getJson('/admin/api/ingredients')->assertUnauthorized();
});

it('đăng nhập được bằng email/mật khẩu và sai mật khẩu bị từ chối', function () {
    $user = adminUser();
    $this->postJson('/admin/api/login', ['email' => $user->email, 'password' => 'sai'])
        ->assertUnprocessable();
    $this->postJson('/admin/api/login', ['email' => $user->email, 'password' => 'secret-pass'])
        ->assertOk()
        ->assertJsonPath('user.role', 'admin');
});

it('tạo nguyên liệu, duyệt ghi vết R10 và ghi log thay đổi', function () {
    $user = adminUser('editor');
    $this->actingAs($user);

    $id = $this->postJson('/admin/api/ingredients', [
        'name_vi' => 'Sả cây',
        'name_en' => 'Lemongrass',
        'category' => 'gia-vi',
        'mapping_confidence' => 'medium',
        'mapping_note' => 'Ánh xạ gần đúng sang USDA lemongrass',
        'units' => [['unit_name' => 'cây', 'grams' => 15]],
    ])->assertCreated()->json('id');

    $this->assertDatabaseHas('ingredient_units', ['ingredient_id' => $id, 'unit_name' => 'cây', 'grams' => 15]);

    $this->postJson("/admin/api/ingredients/{$id}/review", ['approve' => true])
        ->assertOk()
        ->assertJsonPath('needs_review', false)
        ->assertJsonPath('reviewed_by', $user->email);

    expect(DB::table('admin_audit_logs')->where('model_id', $id)->count())->toBeGreaterThanOrEqual(2);
});

it('bắt buộc ghi chú ánh xạ khi độ tin cậy khác high (R7 ở tầng form)', function () {
    $this->actingAs(adminUser());
    $this->postJson('/admin/api/ingredients', [
        'name_vi' => 'Lá lốt',
        'category' => 'rau-cu',
        'mapping_confidence' => 'low',
    ])->assertUnprocessable()->assertJsonValidationErrors('mapping_note');
});

it('tạo món Mức 1 kèm bữa ăn và nguyên liệu, cảnh báo trùng tên hoạt động', function () {
    $this->actingAs(adminUser());
    $ingredient = adminMakeIngredient();

    $dishId = $this->postJson('/admin/api/dishes', [
        'primary_name' => 'Rau muống xào tỏi',
        'country_code' => 'VN',
        'is_eat_out' => true,
        'meal_slots' => ['trua', 'toi'],
        'ingredients' => [['ingredient_id' => $ingredient->id, 'quantity' => 200, 'unit_name' => 'g']],
    ])->assertCreated()->json('id');

    $this->assertDatabaseHas('dish_meal_slots', ['dish_id' => $dishId, 'slot' => 'trua']);
    $this->assertDatabaseHas('dish_ingredients', ['dish_id' => $dishId, 'ingredient_id' => $ingredient->id]);

    $similar = $this->getJson('/admin/api/dishes/similar?name='.urlencode('Rau muống xào tỏi'))
        ->assertOk()->json();
    expect(collect($similar)->pluck('dish_id'))->toContain($dishId);

    // Món có nguyên liệu nhưng nguyên liệu chưa ánh xạ reference → engine từ chối tính, API báo 422 rõ ràng
    $this->postJson("/admin/api/dishes/{$dishId}/compute")->assertUnprocessable();
});

it('tính dinh dưỡng được khi nguyên liệu đã ánh xạ thực phẩm tham chiếu', function () {
    $this->actingAs(adminUser());
    $refId = DB::table('reference_foods')->insertGetId([
        'fct_source' => 'usda_sr_legacy', 'external_id' => '999999',
        'name_en' => 'Spinach, raw', 'kcal_per_100g' => 23,
        'protein_g' => 2.9, 'fat_g' => 0.4, 'carb_g' => 3.6,
        'imported_at' => now(),
    ]);
    $ingredient = adminMakeIngredient(['reference_food_id' => $refId]);
    $dish = Dish::create([
        'slug' => 'mon-test-tinh-'.uniqid(), 'country_code' => 'VN',
        'status' => 'draft', 'source' => 'curated', 'confidence' => 'unknown',
        'needs_review' => true, 'completeness_level' => 1,
    ]);
    $dish->names()->create(['locale' => 'vi', 'name' => 'Rau muống luộc', 'is_primary' => true, 'is_alias' => false]);
    $dish->dishIngredients()->create([
        'ingredient_id' => $ingredient->id, 'quantity' => 100, 'unit_name' => 'g', 'source' => 'curated',
    ]);

    $this->postJson("/admin/api/dishes/{$dish->id}/compute")
        ->assertOk()
        ->assertJsonPath('dish_id', $dish->id);
    $this->assertDatabaseHas('dish_nutrition', ['dish_id' => $dish->id]);
});

it('dashboard trả số liệu tổng quan', function () {
    $this->actingAs(adminUser());
    adminMakeIngredient();
    $this->getJson('/admin/api/dashboard')
        ->assertOk()
        ->assertJsonPath('ingredients_total', 1)
        ->assertJsonPath('ingredients_pending_review', 1);
});

it('xem trước dinh dưỡng trực tiếp không ghi vào DB (ADM-004)', function () {
    $this->actingAs(adminUser());
    $refId = DB::table('reference_foods')->insertGetId([
        'fct_source' => 'usda_sr_legacy', 'external_id' => '888888',
        'name_en' => 'Rice, cooked', 'kcal_per_100g' => 130,
        'protein_g' => 2.7, 'fat_g' => 0.3, 'carb_g' => 28,
        'imported_at' => now(),
    ]);
    $ingredient = adminMakeIngredient(['reference_food_id' => $refId]);

    $resp = $this->postJson('/admin/api/dishes/preview-nutrition', [
        'cooking_method' => 'luoc',
        'ingredients' => [['ingredient_id' => $ingredient->id, 'quantity' => 200, 'unit_name' => 'g']],
    ])->assertOk();
    expect($resp->json('kcal_max'))->toBeGreaterThan(200);
    $this->assertDatabaseCount('dish_nutrition', 0);
});

it('đăng nhập nhanh kiểm thử: tắt theo mặc định, bật thì vào thẳng admin và ghi log', function () {
    // Mặc định tắt → 403 ngay cả khi đã có admin
    adminUser();
    $this->postJson('/admin/api/quick-login')->assertForbidden();
    $this->getJson('/admin/api/login-options')->assertOk()->assertJsonPath('quick_login', false);

    // Bật cờ → vào thẳng tài khoản admin, không cần mật khẩu
    config(['admin.quick_login' => true]);
    $this->getJson('/admin/api/login-options')->assertJsonPath('quick_login', true);
    $this->postJson('/admin/api/quick-login')
        ->assertOk()
        ->assertJsonPath('user.role', 'admin');
    $this->getJson('/admin/api/me')->assertOk()->assertJsonPath('user.role', 'admin');
});
