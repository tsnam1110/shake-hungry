<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hệ thống góp ý / phản hồi dạng ticket (UI-018, yêu cầu chủ dự án
 * 2026-11-01): người dùng đã đăng nhập gửi phản hồi từ trang sản phẩm
 * (món/quả/nguyên liệu); ticket vào hệ thống ở trạng thái "da-ghi-nhan"
 * (đã ghi nhận, chờ duyệt) và được quản trị xử lý qua khu vực admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 16); // gop-y | ho-tro | phat-trien | loi
            $table->string('title')->nullable();
            $table->text('content');
            $table->foreignId('dish_id')->nullable()->constrained('dishes')->nullOnDelete();
            $table->foreignId('fruit_id')->nullable()->constrained('fruits')->nullOnDelete();
            $table->foreignId('ingredient_id')->nullable()->constrained('ingredients')->nullOnDelete();
            $table->string('page')->nullable(); // đường dẫn trang nơi gửi
            $table->string('status', 24)->default('da-ghi-nhan');
            $table->text('admin_note')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_tickets');
    }
};
