<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Media cho nước uống (Phần 3A): mirror dish_media / ingredient_media,
 * cùng ràng buộc R4 về license. File JSON đồ uống là nguồn sự thật (QT-3):
 * mục `media` trong data/drinks/<slug>.json được DrinkSeeder đưa vào bảng
 * này; hiện tại 0/230 mục có media nên bảng trống cho tới khi ảnh từng
 * mục được bổ sung (không gắn ảnh giả, không dùng placeholder làm ảnh thật).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drink_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drink_id')->constrained('drinks')->cascadeOnDelete();
            $table->text('path');
            // R4 — NOT NULL: ảnh không license không vào được database (QT-2).
            $table->string('license', 32);
            $table->text('attribution')->nullable();
            $table->boolean('is_primary')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drink_media');
    }
};
