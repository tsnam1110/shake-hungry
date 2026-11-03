<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Khối chỉ số động cho nước uống (thí điểm đồ uống 2026-11-03, theo yêu cầu
 * của chủ dự án: ghi rõ caffeine dạng mg, và đồ uống không cồn thì không
 * lấp ô thứ tư bằng "Không cồn").
 *
 * - caffeine_mg_min/max + caffeine_mg_note: mg/ly, chỉ khi có căn cứ (khoảng
 *   tầm soát vẫn ghi rõ tầng, không ghi điểm đo giả).
 * - liquid_ml: tổng ml chất lỏng thật, không tính đá, không tính dung tích ly.
 * - key_indicators: JSON [{label, value, value_min, value_max, unit,
 *   per_serving, basis, confidence, source_ref}] — nguồn sự thật vẫn là file
 *   JSON đồ uống, seeder chỉ sao chép, không tính lại.
 * Ô thứ tư trong lưới macro: có cồn thì hiện độ cồn; không cồn thì hiện chỉ
 * số động đầu tiên chưa dùng (thường là kcal/100 ml chất lỏng); không có gì
 * thì hiện "?" như trang món hiện macro thiếu — không hiện "Không cồn" như
 * một chỉ số.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drinks', function (Blueprint $table) {
            $table->decimal('caffeine_mg_min', 8, 2)->nullable()->after('caffeine_level');
            $table->decimal('caffeine_mg_max', 8, 2)->nullable()->after('caffeine_mg_min');
            $table->text('caffeine_mg_note')->nullable()->after('caffeine_mg_max');
            $table->integer('liquid_ml')->nullable()->after('serving_ml');
            $table->json('key_indicators')->nullable()->after('label_note');
        });
    }

    public function down(): void
    {
        Schema::table('drinks', function (Blueprint $table) {
            $table->dropColumn(['caffeine_mg_min', 'caffeine_mg_max', 'caffeine_mg_note', 'liquid_ml', 'key_indicators']);
        });
    }
};
