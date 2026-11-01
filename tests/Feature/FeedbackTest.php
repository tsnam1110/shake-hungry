<?php

declare(strict_types=1);

use App\Models\Dish;
use App\Models\User;

/* UI-018: ticket góp ý/phản hồi — chỉ người đăng nhập, vào trạng thái chờ duyệt. */

it('khách không gửi được phản hồi; người đăng nhập gửi được và ticket ở trạng thái đã ghi nhận chờ duyệt', function () {
    $payload = ['type' => 'gop-y', 'content' => 'Mong có thêm món miền Trung.'];
    $this->postJson('/api/feedback', $payload)->assertUnauthorized();

    $this->actingAs(User::factory()->create(['role' => 'user']));
    $this->postJson('/api/feedback', $payload)
        ->assertCreated()
        ->assertJsonPath('status', 'da-ghi-nhan')
        ->assertJsonPath('status_label', 'Đã ghi nhận · Chờ duyệt')
        ->assertJsonPath('type_label', 'Góp ý');
});

it('phản hồi gắn được với món ăn, kiểm tra loại và độ dài nội dung', function () {
    $this->actingAs(User::factory()->create(['role' => 'user']));
    $dish = Dish::create(['slug' => 'mon-phan-hoi', 'country_code' => 'VN', 'source' => 'curated', 'confidence' => 'low', 'completeness_level' => 1]);

    $this->postJson('/api/feedback', ['type' => 'loi', 'content' => 'Số calo món này có vẻ sai.', 'dish_id' => $dish->id, 'page' => '/mon/mon-phan-hoi'])
        ->assertCreated()
        ->assertJsonPath('type', 'loi');
    $this->getJson('/api/feedback')->assertJsonCount(1)->assertJsonPath('0.context_name', null); // món chưa có tên chính

    $this->postJson('/api/feedback', ['type' => 'khac', 'content' => 'Nội dung đủ dài'])->assertUnprocessable();
    $this->postJson('/api/feedback', ['type' => 'loi', 'content' => 'ngắn'])->assertUnprocessable();
    $this->postJson('/api/feedback', ['type' => 'loi', 'content' => 'Nội dung đủ dài', 'dish_id' => 999999])->assertUnprocessable();
});

it('quản trị thấy ticket chờ duyệt và đổi được trạng thái, người thường thì không', function () {
    $user = User::factory()->create(['role' => 'user']);
    $this->actingAs($user);
    $id = $this->postJson('/api/feedback', ['type' => 'ho-tro', 'content' => 'Tôi cần hỗ trợ đổi mật khẩu.'])
        ->assertCreated()->json('id');
    $this->getJson('/admin/api/feedback')->assertForbidden();

    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $this->getJson('/admin/api/feedback?status=da-ghi-nhan')->assertJsonPath('total', 1);
    $this->postJson("/admin/api/feedback/{$id}/status", ['status' => 'dang-xu-ly', 'admin_note' => 'Đang kiểm tra'])
        ->assertOk()->assertJsonPath('status', 'dang-xu-ly');
    $this->postJson("/admin/api/feedback/{$id}/status", ['status' => 'lung-tung'])->assertUnprocessable();
});
