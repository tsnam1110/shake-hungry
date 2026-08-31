<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tạo sẵn cho GĐ6 (SETUP-003 mục 6). Gán ở tầng NGUYÊN LIỆU, không phải món (QT-6).
        Schema::create('ingredient_tcm_traits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnDelete();
            $table->string('nature', 8)->nullable();
            $table->string('flavor', 8)->nullable();
            $table->string('element', 8)->nullable();
            // R5 — NOT NULL: nhãn tính vị không nguồn thì không được nhập (QT-6).
            $table->text('source_ref');
            $table->string('confidence', 8);
            $table->timestampsTz();
        });

        DB::statement("ALTER TABLE ingredient_tcm_traits ADD CONSTRAINT tcm_nature_check CHECK (nature IS NULL OR nature IN ('han', 'luong', 'binh', 'on', 'nhiet'))");
        DB::statement("ALTER TABLE ingredient_tcm_traits ADD CONSTRAINT tcm_flavor_check CHECK (flavor IS NULL OR flavor IN ('toan', 'kho', 'cam', 'tan', 'ham', 'dam'))");
        DB::statement("ALTER TABLE ingredient_tcm_traits ADD CONSTRAINT tcm_element_check CHECK (element IS NULL OR element IN ('moc', 'hoa', 'tho', 'kim', 'thuy'))");

        Schema::create('dish_tcm_summary', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dish_id')->unique()->constrained('dishes')->cascadeOnDelete();
            $table->decimal('nature_score', 4, 2);
            $table->string('dominant_element', 8)->nullable();
            $table->decimal('coverage_pct', 5, 2);
            $table->string('confidence', 8);
            $table->timestampTz('computed_at');
        });

        // coverage_pct < 60 ⇒ confidence tối đa 'low' (DATA-MODEL §4).
        DB::statement("ALTER TABLE dish_tcm_summary ADD CONSTRAINT dish_tcm_coverage_check CHECK (coverage_pct >= 60 OR confidence IN ('low', 'unknown'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('dish_tcm_summary');
        Schema::dropIfExists('ingredient_tcm_traits');
    }
};
