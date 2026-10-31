<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Yêu thích + nhật ký ăn hoa quả (UI-014, yêu cầu của chủ dự án 2026-10-31):
 * favorites giữ món/quả người dùng đánh dấu tim; fruit_logs là bản ghi mỗi
 * lần ăn quả (đối tác của meal_logs phía món ăn) để làm thống kê số lượng.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('dish_id')->nullable()->constrained('dishes')->cascadeOnDelete();
            $table->foreignId('fruit_id')->nullable()->constrained('fruits')->cascadeOnDelete();
            $table->timestampsTz();
            $table->unique(['user_id', 'dish_id']);
            $table->unique(['user_id', 'fruit_id']);
        });

        Schema::create('fruit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('fruit_id')->constrained('fruits')->cascadeOnDelete();
            $table->timestampTz('eaten_at');
            $table->string('source', 16)->default('manual');
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fruit_logs');
        Schema::dropIfExists('favorites');
    }
};
