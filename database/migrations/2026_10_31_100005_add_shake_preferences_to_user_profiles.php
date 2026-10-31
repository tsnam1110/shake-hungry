<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tuỳ chọn lắc món lưu trong hồ sơ (UI-011, yêu cầu của chủ dự án 2026-10-31):
 * vùng miền yêu thích + kcal mục tiêu mỗi bữa — trang chủ điền sẵn từ hồ sơ
 * và lưu ngược khi người dùng đổi ngay tại khối Lắc.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->foreignId('preferred_region_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->unsignedSmallInteger('target_kcal_per_meal')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('preferred_region_id');
            $table->dropColumn('target_kcal_per_meal');
        });
    }
};
