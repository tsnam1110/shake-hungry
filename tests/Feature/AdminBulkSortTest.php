<?php

declare(strict_types=1);

use App\Models\Contribution;
use App\Models\Dish;
use App\Models\Fruit;
use App\Models\Ingredient;
use App\Models\User;

/*
 * ADM-008 (đợt 2): sắp xếp theo cột ở phía máy chủ + thao tác hàng loạt
 * (duyệt / mở lại / xoá / tính calo / xử lý đóng góp) cho trang quản trị.
 */

function bulkAdmin(): User
{
    return User::factory()->create(['role' => 'admin']);
}

function bulkIngredient(array $overrides = []): Ingredient
{
    return Ingredient::create(array_merge([
        'name_vi' => 'Nguyên liệu '.uniqid(),
        'name_en' => 'Ingredient',
        'slug' => 'nl-'.uniqid(),
        'category' => 'rau-cu',
        'mapping_confidence' => 'high',
        'source' => 'curated',
        'confidence' => 'high',
        'needs_review' => true,
        'nutrition_snapshot' => ['kcal_per_100g' => 20.0, 'fct_source' => 'usda_sr_legacy'],
    ], $overrides));
}

function bulkDish(array $overrides = []): Dish
{
    return Dish::create(array_merge([
        'slug' => 'mon-'.uniqid(),
        'country_code' => 'VN',
        'cooking_method' => 'nau',
        'completeness_level' => 1,
        'is_eat_out' => true,
        'is_home_cook' => true,
        'source' => 'curated',
        'confidence' => 'medium',
        'needs_review' => true,
    ], $overrides));
}

test('sắp xếp nguyên liệu theo tên giảm dần và theo nhóm', function () {
    $this->actingAs(bulkAdmin());
    bulkIngredient(['name_vi' => 'Bắp cải']);
    bulkIngredient(['name_vi' => 'Ớt chuông']);
    bulkIngredient(['name_vi' => 'Cà rốt']);

    $this->getJson('/admin/api/ingredients?sort=name_vi&dir=desc')
        ->assertOk()
        ->assertJsonPath('data.0.name_vi', 'Ớt chuông')
        ->assertJsonPath('data.2.name_vi', 'Bắp cải');
    $this->getJson('/admin/api/ingredients?sort=khong-hop-le')
        ->assertOk()
        ->assertJsonPath('data.0.name_vi', 'Bắp cải'); // cột lạ → về mặc định tên tăng dần
});

test('sắp xếp món theo tên chính tiếng Việt', function () {
    $this->actingAs(bulkAdmin());
    $a = bulkDish();
    $a->names()->create(['locale' => 'vi', 'name' => 'Xôi xéo', 'is_primary' => true, 'is_alias' => false]);
    $b = bulkDish();
    $b->names()->create(['locale' => 'vi', 'name' => 'Bún bò', 'is_primary' => true, 'is_alias' => false]);

    $this->getJson('/admin/api/dishes?sort=name&dir=asc')
        ->assertOk()
        ->assertJsonPath('data.0.id', $b->id);
    $this->getJson('/admin/api/dishes?sort=name&dir=desc')
        ->assertOk()
        ->assertJsonPath('data.0.id', $a->id);
});

test('bulk nguyên liệu: duyệt hàng loạt và xoá hàng loạt', function () {
    $this->actingAs(bulkAdmin());
    $a = bulkIngredient();
    $b = bulkIngredient();
    $c = bulkIngredient();

    $this->postJson('/admin/api/ingredients/bulk', ['action' => 'approve', 'ids' => [$a->id, $b->id]])
        ->assertOk()->assertJsonPath('affected', 2);
    expect($a->fresh()->needs_review)->toBeFalse()
        ->and($b->fresh()->needs_review)->toBeFalse()
        ->and($c->fresh()->needs_review)->toBeTrue();

    $this->postJson('/admin/api/ingredients/bulk', ['action' => 'delete', 'ids' => [$c->id]])
        ->assertOk()->assertJsonPath('affected', 1);
    expect(Ingredient::find($c->id))->toBeNull();

    $this->postJson('/admin/api/ingredients/bulk', ['action' => 'sai-action', 'ids' => [$a->id]])
        ->assertUnprocessable();
});

test('bulk món: duyệt + tính calo bỏ qua món chưa có nguyên liệu', function () {
    $this->actingAs(bulkAdmin());
    $dish = bulkDish();
    $ingredient = bulkIngredient();
    $dish->dishIngredients()->create([
        'ingredient_id' => $ingredient->id, 'quantity' => 100, 'unit_name' => 'g',
        'source' => 'curated', 'confidence' => 'high',
    ]);
    $empty = bulkDish();

    $this->postJson('/admin/api/dishes/bulk', ['action' => 'compute', 'ids' => [$dish->id, $empty->id]])
        ->assertOk()
        ->assertJsonPath('affected', 1)
        ->assertJsonPath('skipped', 1);
    expect($dish->fresh()->nutrition)->not->toBeNull();

    $this->postJson('/admin/api/dishes/bulk', ['action' => 'approve', 'ids' => [$dish->id, $empty->id]])
        ->assertOk()->assertJsonPath('affected', 2);
    expect($empty->fresh()->needs_review)->toBeFalse();
});

test('bulk quả: duyệt hàng loạt', function () {
    $this->actingAs(bulkAdmin());
    $ingredient = bulkIngredient(['category' => 'trai-cay']);
    $fruit = Fruit::create([
        'slug' => $ingredient->slug, 'ingredient_id' => $ingredient->id,
        'classification_group' => 'mong', 'origin' => 'nhap-khau',
        'availability_months' => [1], 'availability_type' => 'quanh-nam-nhap-khau',
        'needs_review' => true,
    ]);

    $this->postJson('/admin/api/fruits/bulk', ['action' => 'approve', 'ids' => [$fruit->id]])
        ->assertOk()->assertJsonPath('affected', 1);
    expect($fruit->fresh()->needs_review)->toBeFalse();
});

test('bulk đóng góp: chỉ xử lý đề xuất đang chờ, món mới được tạo nháp', function () {
    $this->actingAs(bulkAdmin());
    $pending = Contribution::create(['type' => 'new_dish', 'payload' => ['title' => 'Món bulk mới', 'detail' => 'x'], 'status' => 'pending']);
    $done = Contribution::create(['type' => 'fix_dish', 'payload' => ['detail' => 'y'], 'status' => 'approved']);

    $this->postJson('/admin/api/contributions/bulk', ['action' => 'approve', 'ids' => [$pending->id, $done->id]])
        ->assertOk()
        ->assertJsonPath('affected', 1)
        ->assertJsonPath('skipped', 1);
    expect($pending->fresh()->status)->toBe('approved')
        ->and(Dish::where('slug', 'mon-bulk-moi')->exists())->toBeTrue();
});
