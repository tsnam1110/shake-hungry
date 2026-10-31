<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nhánh Hoa quả (FRUIT-MODULE.md, FRUIT-001).
 *
 * `fruits` là LỚP HỒ SƠ phủ lên nguyên liệu nhóm trai-cay (1:1 qua
 * ingredient_id) — dinh dưỡng/dị ứng vẫn lấy từ nguyên liệu, hồ sơ chỉ giữ
 * phần thị trường–mùa vụ–canh tác–kết hợp. `fruit_prices` giữ khoảng giá
 * tham khảo theo miền × tháng, bắt buộc có nguồn + ngày thu thập
 * (kỷ luật giá FRUIT-MODULE §3). File data/fruits/*.json là nguồn sự thật
 * (QT-3); hai bảng này chỉ là bản dựng lại.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fruits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->unique()->constrained('ingredients')->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('classification_group');
            $table->string('family')->nullable();
            $table->string('origin'); // trong-nuoc | nhap-khau | ca-hai
            $table->json('grown_in')->nullable(); // [{region, note}]
            $table->json('availability_months'); // [1..12]
            $table->json('main_season_months')->nullable();
            $table->string('availability_type'); // theo-mua | quanh-nam | quanh-nam-nhap-khau
            $table->json('best_nutrition_months')->nullable();
            $table->text('best_nutrition_note')->nullable();
            $table->json('best_value_months')->nullable();
            $table->text('best_value_note')->nullable();
            $table->json('pairing_cautions')->nullable(); // [{with, level: co-so|dan-gian, note}]
            $table->text('selection_note')->nullable();
            $table->text('storage_note')->nullable();
            $table->boolean('needs_review')->default(true);
            $table->string('reviewed_by')->nullable();
            $table->date('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();
        });

        Schema::create('fruit_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fruit_id')->constrained('fruits')->cascadeOnDelete();
            $table->string('region_code'); // bac | trung | nam
            $table->unsignedTinyInteger('month'); // 1..12
            $table->unsignedInteger('price_min'); // VND/kg
            $table->unsignedInteger('price_max'); // VND/kg
            $table->string('source_name');
            $table->string('source_url')->nullable();
            $table->date('collected_at');
            $table->timestamps();

            $table->unique(['fruit_id', 'region_code', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fruit_prices');
        Schema::dropIfExists('fruits');
    }
};
