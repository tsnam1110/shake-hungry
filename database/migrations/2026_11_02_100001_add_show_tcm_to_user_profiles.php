<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tuỳ chọn hiển thị lớp tính vị của người dùng (TCM-003 mục 1 — "bật/tắt
 * trong cài đặt người dùng"): chủ dự án đã chốt 2026-11-01 hiển thị tính vị
 * MẶC ĐỊNH BẬT, nên cột này mặc định true; người dùng không muốn xem có thể
 * tắt trong hồ sơ. Khách (chưa đăng nhập) luôn thấy như mặc định.
 * Lớp tính vị chỉ là hiển thị văn hoá — tuỳ chọn này không đụng engine gợi ý.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->boolean('show_tcm')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn('show_tcm');
        });
    }
};
