<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Chia sẻ tuần ăn bằng mã chỉ-đọc (Giai đoạn 4, Task 2 — theo lệnh duyệt
 * toàn bộ lộ trình 2026-11-04, mô hình mã chỉ-đọc kiểu Mealbird).
 *
 * - Chỉ lưu hash SHA-256 của mã, KHÔNG lưu mã gốc: mã chỉ hiện một lần
 *   lúc tạo, mất mã thì tạo mã mới. Rò rỉ DB không lộ mã đang còn hiệu lực.
 * - Mã hết hạn sau 14 ngày (giống Mealbird), thu hồi được bất cứ lúc nào
 *   (revoked_at). Người xem mã KHÔNG đăng nhập, chỉ thấy món và đi chợ
 *   của tuần đó — không thấy mục tiêu, ngân sách, hồ sơ, dị ứng hay
 *   thông tin người dùng (xem WeekPlanShareController::showPublic).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('week_plan_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('week_plan_id')->constrained('week_plans')->cascadeOnDelete();
            $table->string('code_hash', 64)->unique();
            $table->timestampTz('expires_at');
            $table->timestampTz('revoked_at')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('week_plan_shares');
    }
};
