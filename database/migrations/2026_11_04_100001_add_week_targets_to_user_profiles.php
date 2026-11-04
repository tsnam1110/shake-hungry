<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mục tiêu tuần ăn (Giai đoạn 2, Phần 2.1, Task 1 — theo 5 quyết định đã chốt
 * ngày 2026-11-04):
 * - `goal`: mục tiêu chung của người dùng (giam / duy-tri / tang). Chỉ lưu,
 *   chưa dùng để suy kcal trong Task 1 — tỉ lệ điều chỉnh kcal theo mục tiêu
 *   chưa được chốt, nên không tự áp -20% hay bất kỳ mức nào khác.
 * - `target_protein_g_per_day` / `target_kcal_per_day`: mục tiêu do người dùng
 *   nhập/ghi đè (quyết định "làm cả 2": app gợi ý protein bằng
 *   WeekTargetSuggester, người dùng tự nhập/ghi đè). Null = chưa đặt.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->string('goal', 16)->nullable()->after('target_kcal_per_meal');
            $table->unsignedSmallInteger('target_protein_g_per_day')->nullable()->after('goal');
            $table->unsignedSmallInteger('target_kcal_per_day')->nullable()->after('target_protein_g_per_day');
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn(['goal', 'target_protein_g_per_day', 'target_kcal_per_day']);
        });
    }
};
