<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cờ "chưa kiểm định" cho số dinh dưỡng của món (quyết định chủ dự án 2026-10-31).
 *
 * Chủ dự án mở lại các nguồn thứ cấp (Bảng TPTP Việt Nam, bảng FCT nước ngoài,
 * văn liệu khoa học — xem DATA-PIPELINE §7.2 tầng 6) ở chế độ KHÔNG ưu tiên:
 * chỉ dùng khi nguồn chính thống không có dữ liệu. Điều kiện đi kèm: mọi con số
 * có đóng góp từ nguồn thứ cấp phải được ĐÁNH DẤU và HIỂN THỊ cho người dùng
 * biết là "đã có nhưng chưa được kiểm định".
 *
 * Cột này lưu danh sách slug nguyên liệu dùng nguồn thứ cấp đã thực sự tham gia
 * phép tính của món (không nằm trong missing_ingredients), do
 * DishNutritionService ghi tại thời điểm tính. Giao diện công khai đọc cột này
 * để hiện cảnh báo; khi một nguyên liệu được thay bằng nguồn chính thống và món
 * được tính lại, danh sách tự rỗng — dấu vết kiểm định luôn khớp con số đang hiển thị.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dish_nutrition', function (Blueprint $table) {
            $table->json('unverified_ingredients')->nullable()->after('missing_ingredients');
        });
    }

    public function down(): void
    {
        Schema::table('dish_nutrition', function (Blueprint $table) {
            $table->dropColumn('unverified_ingredients');
        });
    }
};
