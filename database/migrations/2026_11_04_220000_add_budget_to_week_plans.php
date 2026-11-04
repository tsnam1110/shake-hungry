<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ngân sách mềm cho tuần ăn (Giai đoạn 4, Task 1 — theo lệnh duyệt toàn
 * bộ lộ trình 2026-11-04).
 *
 * `budget_vnd` chỉ là MỤC TIÊU do người dùng đặt, kiểu ngân sách mềm của
 * Mealbird: tuần vẫn giao kèm cảnh báo, không chặn. Nó KHÔNG phải tổng
 * tiền thật của tuần, và hệ thống KHÔNG được kết luận tuần này dưới hay
 * vượt ngân sách, vì chưa có bảng giá/gói đủ nguồn để tính tổng tiền
 * thật (xem WEEK-001 § Giai đoạn 4). Khi nào có bảng giá theo kênh +
 * ngày đủ nguồn, tổng tiền mới được tính và so sánh ở đây.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('week_plans', function (Blueprint $table) {
            $table->unsignedInteger('budget_vnd')->nullable()->after('target_kcal_per_day');
        });
    }

    public function down(): void
    {
        Schema::table('week_plans', function (Blueprint $table) {
            $table->dropColumn('budget_vnd');
        });
    }
};
