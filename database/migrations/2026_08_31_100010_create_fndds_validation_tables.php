<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * DATA-003 — đồ thị món→nguyên liệu→gram của USDA FNDDS (SR Links).
     *
     * ĐÂY KHÔNG PHẢI DỮ LIỆU HIỂN THỊ. Là bộ kiểm chứng engine calo (TEST-002):
     * cả nguyên liệu lẫn kcal công bố đều đến từ USDA, nên engine lệch nhiều ở đây
     * nghĩa là ENGINE SAI, không phải dữ liệu sai. Bảng tách riêng khỏi `dishes`
     * để không bao giờ lẫn vào kho món Việt (cạm bẫy ghi ở DATA-003).
     */
    public function up(): void
    {
        Schema::create('fndds_foods', function (Blueprint $table) {
            $table->id();
            $table->string('food_code', 16)->unique();
            $table->string('fdc_id', 16);
            $table->text('description');
            $table->string('wweia_category', 16)->nullable();
            $table->decimal('kcal_per_100g', 8, 2);
            $table->decimal('protein_g', 8, 2)->nullable();
            $table->decimal('fat_g', 8, 2)->nullable();
            $table->decimal('carb_g', 8, 2)->nullable();
            $table->decimal('fiber_g', 8, 2)->nullable();
            // Cách chế biến suy từ mô tả — TEST-002 bắt buộc tách kết quả theo nhóm này.
            $table->string('cooking_method', 24)->nullable();
            $table->timestampTz('imported_at');

            $table->index('cooking_method');
        });

        Schema::create('fndds_food_inputs', function (Blueprint $table) {
            $table->id();
            $table->string('food_code', 16);
            $table->string('sr_code', 16);           // NDB number của nguyên liệu
            $table->string('sr_fdc_id', 16)->nullable(); // fdc_id SR Legacy tương ứng
            $table->text('sr_description');
            $table->decimal('gram_weight', 10, 3);   // gram trên 100 g món
            $table->string('retention_code', 8)->nullable();

            $table->index('food_code');
            $table->index('sr_fdc_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fndds_food_inputs');
        Schema::dropIfExists('fndds_foods');
    }
};
