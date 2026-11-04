<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Mô hình tuần ăn (Giai đoạn 2, Phần 2.2 — theo kế hoạch đã duyệt toàn bộ
 * ngày 2026-11-04, không hỏi duyệt giữa chừng nữa).
 *
 * - `week_plans`: một tuần của một người dùng, bắt đầu ngày `start_date`,
 *   kéo dài đúng 7 ngày tới `end_date`. Ba cột mục tiêu là SNAPSHOT lấy
 *   từ hồ sơ lúc tạo tuần: đổi hồ sơ sau đó không sửa ngược tuần cũ,
 *   giống Mealbird giữ tuần đã giao. Null = người dùng chưa đặt mục tiêu.
 * - `week_plan_days`: tổng ngày đã tính (protein, khoảng kcal) và trạng
 *   thái `dat` / `thieu` / `chua-du-du-lieu`. Tổng này là ảnh chụp lúc
 *   tạo/sửa tuần; validator ở Phần 2.3 tính lại từ món để kiểm chứng,
 *   không tin mù số đã lưu khi hiển thị.
 * - `week_plan_meals`: đúng một món cho mỗi (ngày, bữa) — unique
 *   (week_plan_day_id, slot). `servings` mặc định 1 khẩu phần; `is_eat_out`
 *   chỉ là dấu đánh dấu bữa ăn ngoài, không tự đổi món còn lại trong ngày.
 *   `replaced_from_dish_id` ghi món gốc khi đổi bữa (Phần 2.5), null nếu
 *   món này do builder chọn từ đầu.
 * - Chỉ món đủ protein + kcal mới được builder đưa vào tuần (quyết định
 *   đã chốt); validator vẫn phải xử lý món không đủ khi người dùng đổi
 *   bữa thủ công — ngày đó thành `chua-du-du-lieu`, không thành `dat`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('week_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('goal', 16)->nullable();
            $table->unsignedSmallInteger('target_protein_g_per_day')->nullable();
            $table->unsignedSmallInteger('target_kcal_per_day')->nullable();
            $table->string('status', 16)->default('active');
            $table->timestampsTz();
            $table->unique(['user_id', 'start_date']);
        });
        DB::statement("ALTER TABLE week_plans ADD CONSTRAINT week_plans_goal_check CHECK (goal IS NULL OR goal IN ('giam', 'duy-tri', 'tang'))");
        DB::statement("ALTER TABLE week_plans ADD CONSTRAINT week_plans_status_check CHECK (status IN ('active', 'archived'))");

        Schema::create('week_plan_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('week_plan_id')->constrained('week_plans')->cascadeOnDelete();
            $table->date('date');
            $table->decimal('protein_g', 8, 2)->nullable();
            $table->decimal('kcal_min', 8, 2)->nullable();
            $table->decimal('kcal_max', 8, 2)->nullable();
            $table->string('status', 24)->default('chua-du-du-lieu');
            $table->timestampsTz();
            $table->unique(['week_plan_id', 'date']);
        });
        DB::statement("ALTER TABLE week_plan_days ADD CONSTRAINT week_plan_days_status_check CHECK (status IN ('dat', 'thieu', 'chua-du-du-lieu'))");

        Schema::create('week_plan_meals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('week_plan_day_id')->constrained('week_plan_days')->cascadeOnDelete();
            $table->foreignId('dish_id')->constrained('dishes')->restrictOnDelete();
            $table->string('slot', 8);
            $table->decimal('servings', 4, 2)->default(1);
            $table->boolean('is_eat_out')->default(false);
            $table->foreignId('replaced_from_dish_id')->nullable()->constrained('dishes')->restrictOnDelete();
            $table->timestampsTz();
            $table->unique(['week_plan_day_id', 'slot']);
        });
        DB::statement("ALTER TABLE week_plan_meals ADD CONSTRAINT week_plan_meals_slot_check CHECK (slot IN ('sang', 'trua', 'chieu', 'toi', 'an-vat'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('week_plan_meals');
        Schema::dropIfExists('week_plan_days');
        Schema::dropIfExists('week_plans');
    }
};
