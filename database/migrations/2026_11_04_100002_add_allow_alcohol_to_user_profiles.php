<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lọc tuổi cho đồ uống có cồn (quy định cocktail, bước 2/4 — quyết định
 * chủ dự án 2026-11-04):
 * - `allow_alcohol`: người dùng tự bật, mặc định TẮT. Chỉ bật khi người
 *   dùng xác nhận đã đủ 18 tuổi. Không suy từ `birth_year` (năm sinh chỉ
 *   dùng cho TDEE, và có thể trống), không tự bật theo bất kỳ dữ liệu nào
 *   khác. Khách chưa đăng nhập không có hồ sơ nên luôn coi như tắt.
 * - Phạm vi hiện tại: chỉ là cờ đã lưu, chưa làm thay đổi kết quả Lắc —
 *   Lắc vẫn chỉ gợi ý Dish (bước 1), chưa có tính năng gợi ý Drink. Cờ
 *   này là cổng bắt buộc cho mọi tính năng gợi ý đồ uống có cồn trong
 *   tương lai: tính năng đó chỉ được đưa Drink alcohol=true vào pool khi
 *   cờ này đang bật. Thư viện đồ uống không đổi: vẫn giữ công tắc
 *   "Không cồn trước" và huy hiệu 18+ (bước 3).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->boolean('allow_alcohol')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn('allow_alcohol');
        });
    }
};
