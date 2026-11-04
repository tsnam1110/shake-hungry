<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\File;

/*
 * API quản trị 3 module file (Giai đoạn 1 thay /quan-tri bằng /admin):
 * đọc/ghi trực tiếp file data/ (QT-3), chỉ trường ghi chú/cờ an toàn.
 *
 * Test PUT dùng một file kết hợp thật và KHÔI PHỤC nguyên vẹn trong
 * finally — không để lại thay đổi nào trong data/ sau khi test chạy.
 */

function moduleAdminUser(): User
{
    return User::factory()->create(['role' => 'admin', 'password' => 'secret-pass']);
}

it('chặn khách chưa đăng nhập khỏi API module file', function () {
    $this->getJson('/admin/api/modules/ket-hop')->assertUnauthorized();
    $this->getJson('/admin/api/modules/ket-hop/dua-leo--ca-chua')->assertUnauthorized();
});

it('liệt kê và đọc chi tiết một mục kết hợp từ file', function () {
    $user = moduleAdminUser();
    $this->actingAs($user);

    $this->getJson('/admin/api/modules/ket-hop?q=dua-leo')
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.slug', 'dua-leo--ca-chua');

    $this->getJson('/admin/api/modules/ket-hop/dua-leo--ca-chua')
        ->assertOk()
        ->assertJsonPath('slug', 'dua-leo--ca-chua')
        ->assertJsonPath('science_status', 'unproven');
});

it('trả 404 cho module lạ, slug lạ và slug chứa path traversal', function () {
    $user = moduleAdminUser();
    $this->actingAs($user);

    $this->getJson('/admin/api/modules/khong-co')->assertNotFound();
    $this->getJson('/admin/api/modules/ket-hop/khong-co-muc-nay')->assertNotFound();
    $this->getJson('/admin/api/modules/ket-hop/..%2F..%2Fcomposer')->assertNotFound();
});

it('sửa ghi chú qua API và chỉ ghi đúng dòng đó, rồi khôi phục file', function () {
    $user = moduleAdminUser();
    $this->actingAs($user);

    $path = base_path('data/combinations/dua-leo--ca-chua.json');
    $backup = (string) File::get($path);
    try {
        $this->putJson('/admin/api/modules/ket-hop/dua-leo--ca-chua', [
            'condition_vi' => 'Ghi chú kiểm thử — sẽ khôi phục.',
            // Trường ngoài phạm vi: phải bị loại, không được ghi vào file.
            'slug' => 'doi-slug-trai-phep',
            'side_a' => ['doi', 'dinh', 'danh'],
        ])->assertOk()
            ->assertJsonPath('data.condition_vi', 'Ghi chú kiểm thử — sẽ khôi phục.');

        $after = json_decode((string) File::get($path), true);
        expect($after['condition_vi'])->toBe('Ghi chú kiểm thử — sẽ khôi phục.')
            ->and($after['slug'])->toBe('dua-leo--ca-chua')
            ->and($after['side_a'])->not->toBe(['doi', 'dinh', 'danh']);
    } finally {
        File::put($path, $backup);
    }
    expect((string) File::get($path))->toBe($backup);
});

it('từ chối science_status ngoài danh sách cho phép', function () {
    $user = moduleAdminUser();
    $this->actingAs($user);

    $this->putJson('/admin/api/modules/ket-hop/dua-leo--ca-chua', [
        'science_status' => 'cam-chac-chan',
    ])->assertUnprocessable();
});
