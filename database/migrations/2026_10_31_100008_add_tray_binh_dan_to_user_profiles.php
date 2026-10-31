<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tuỳ chọn "Bữa cơm bình dân" cho mâm cơm tự nấu (REC-004, yêu cầu của chủ
 * dự án 2026-10-31): khi bật, mâm chỉ gồm món dễ nấu với nguyên liệu phổ
 * biến dễ kiếm như bữa cơm nhà hằng ngày. Trang chủ điền sẵn từ hồ sơ và
 * lưu ngược khi đổi, cùng khuôn với tuỳ chọn mâm cơm của REC-003.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->boolean('tray_binh_dan')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn('tray_binh_dan');
        });
    }
};
