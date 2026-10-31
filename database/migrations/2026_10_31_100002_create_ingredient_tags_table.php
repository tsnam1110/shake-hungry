<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gắn nhãn cho nguyên liệu (TAXONOMY.md).
 *
 * Bảng `tags` + `dish_tags` đã có từ schema gốc (2026_08_31_100005) nhưng mới
 * dùng cho món. Nguyên liệu cũng cần nhãn (nguồn gốc, dị ứng, trạng thái) vì
 * nhãn dị ứng/chế độ ăn của MÓN được suy từ nhãn của NGUYÊN LIỆU — thiếu bảng
 * này thì phép suy không có chỗ đứng. Cấu trúc chép đúng khuôn dish_tags.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredient_tags', function (Blueprint $table) {
            $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();

            $table->unique(['ingredient_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredient_tags');
    }
};
