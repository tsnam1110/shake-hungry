<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FOOD-COMBINE-001: bảng cặp kết hợp cần lưu ý (data/combinations/*.json là
 * nguồn sự thật — QT-3; bảng này dựng lại từ file qua CombinationSeeder).
 * Mỗi bản ghi là MỘT cặp duy nhất hiển thị hai chiều; tầng hiển thị KHÔNG
 * lưu tay mà suy ra từ evidence_class bằng hàm thuần trên model.
 * Lớp này chỉ là thông tin — không bao giờ vào bộ lọc/luồng gợi ý.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('combinations', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 96)->unique();
            // Mỗi phía: ingredient | fruit | food_group | drug_group.
            $table->string('side_a_type', 16);
            $table->string('side_a_ref', 96);
            $table->string('side_a_label', 160)->nullable();
            $table->jsonb('side_a_members')->nullable();
            $table->string('side_b_type', 16);
            $table->string('side_b_ref', 96);
            $table->string('side_b_label', 160)->nullable();
            $table->jsonb('side_b_members')->nullable();
            $table->string('category', 16); // food_food | drug_food
            $table->string('evidence_class', 2); // E1 | E2 | E3
            $table->string('science_status', 16); // supported | conditional | unproven | refuted
            $table->text('condition_vi')->nullable();
            $table->text('mechanism_vi')->nullable();
            $table->jsonb('traditional_source')->nullable();
            $table->jsonb('sources');
            $table->boolean('needs_review')->default(true);
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('combinations');
    }
};
