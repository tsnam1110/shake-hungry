<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Media cho nguyên liệu (IMG-001): mirror dish_media để hoa quả/nguyên liệu
 * cũng có ảnh như món ăn. R4 giữ nguyên — license NOT NULL, ảnh không có
 * license không vào được database. Hoa quả dùng chung media của nguyên liệu
 * (hồ sơ quả trỏ ingredient_slug).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredient_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnDelete();
            $table->text('path');
            // R4 — NOT NULL: ảnh không license không vào được database (QT-2).
            $table->string('license', 32);
            $table->text('attribution')->nullable();
            $table->boolean('is_primary')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredient_media');
    }
};
