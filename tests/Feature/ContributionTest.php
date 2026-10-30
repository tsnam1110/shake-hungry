<?php

declare(strict_types=1);

use App\Models\Contribution;
use App\Models\User;

/* CTR-001: đóng góp vào hàng đợi, chỉ admin duyệt mới thành dữ liệu. */

it('người dùng gửi đề xuất, đề xuất chờ duyệt không xuất hiện ở kho món công khai', function () {
    $user = User::factory()->create(['role' => 'user']);
    $this->actingAs($user);
    $this->postJson('/api/contributions', ['type' => 'new_dish', 'title' => 'Bún cá rô đồng', 'detail' => 'Món Hải Dương'])
        ->assertCreated()
        ->assertJsonPath('status', 'pending');
    $this->getJson('/api/dishes?q='.urlencode('Bún cá rô'))->assertOk()->assertJsonCount(0, 'data');
});

it('admin chấp nhận món mới thì tạo món nháp curated chờ biên soạn, từ chối ghi lý do', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $c1 = Contribution::create(['type' => 'new_dish', 'payload' => ['title' => 'Canh chua cá lóc', 'submitted_by' => 'a@b.c'], 'status' => 'pending']);
    $c2 = Contribution::create(['type' => 'new_place', 'payload' => ['title' => 'Quán X', 'submitted_by' => 'a@b.c'], 'status' => 'pending']);
    $this->actingAs($admin);

    $resp = $this->postJson("/admin/api/contributions/{$c1->id}/review", ['decision' => 'approved'])->assertOk();
    $dishId = $resp->json('created_dish_id');
    expect($dishId)->not->toBeNull();
    $this->assertDatabaseHas('dishes', ['id' => $dishId, 'source' => 'curated', 'status' => 'draft', 'needs_review' => true]);

    $this->postJson("/admin/api/contributions/{$c2->id}/review", ['decision' => 'rejected', 'reject_reason' => 'Thiếu địa chỉ cụ thể'])
        ->assertOk()
        ->assertJsonPath('status', 'rejected')
        ->assertJsonPath('reject_reason', 'Thiếu địa chỉ cụ thể');

    // Đã xử lý thì không xử lý lại
    $this->postJson("/admin/api/contributions/{$c1->id}/review", ['decision' => 'approved'])->assertUnprocessable();
});
