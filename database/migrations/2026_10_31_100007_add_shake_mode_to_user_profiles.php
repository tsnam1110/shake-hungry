<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tuỳ chọn lắc hai chế độ (REC-003, yêu cầu của chủ dự án 2026-10-31):
 * chế độ lắc mặc định của người dùng (ăn ngoài / mâm cơm tự nấu) và số món
 * trong mâm (1–4, không tính cơm) — trang chủ điền sẵn từ hồ sơ và lưu
 * ngược khi đổi, cùng khuôn với tuỳ chọn lắc của UI-011.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->string('shake_mode', 16)->nullable();
            $table->unsignedTinyInteger('tray_dish_count')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn(['shake_mode', 'tray_dish_count']);
        });
    }
};
