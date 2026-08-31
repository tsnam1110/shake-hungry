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
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 64)->unique();
            $table->text('name_vi');
            $table->text('name_en')->nullable();
            $table->foreignId('reference_food_id')->nullable()->constrained('reference_foods')->restrictOnDelete();
            $table->string('mapping_confidence', 8);
            $table->text('mapping_note')->nullable();
            $table->string('category', 32);
            $table->decimal('density_g_per_ml', 6, 3)->nullable();
            $table->decimal('default_yield_factor', 4, 3)->nullable();
            $table->decimal('oil_absorption_pct', 5, 2)->nullable();
            $table->boolean('is_ready_to_eat')->default(false);
            $table->text('density_note')->nullable();
            $table->text('source_url')->nullable();
            $table->text('scientific_name')->nullable();
            $table->jsonb('nutrition_snapshot')->nullable();
            $table->jsonb('alternatives')->default('[]');
            $table->jsonb('secondary_sources')->default('[]');
            $table->string('source', 16);
            $table->string('confidence', 8);
            $table->boolean('needs_review')->default(false);
            $table->timestampsTz();
        });

        DB::statement("ALTER TABLE ingredients ADD CONSTRAINT ingredients_mapping_confidence_check CHECK (mapping_confidence IN ('high', 'medium', 'low'))");
        // Danh mục thực tế trong data/ingredients/ có thêm 'trai-cay' và 'rau-gia-vi'
        // so với bảng trong DATA-REPOSITORY §3 — đã đính chính tài liệu cùng commit (QT-7).
        DB::statement("ALTER TABLE ingredients ADD CONSTRAINT ingredients_category_check CHECK (category IN ('thit', 'thuy-san', 'rau-cu', 'rau-gia-vi', 'gia-vi', 'tinh-bot', 'dau-mo', 'trai-cay', 'khac'))");
        DB::statement("ALTER TABLE ingredients ADD CONSTRAINT ingredients_source_check CHECK (source IN ('imported', 'curated', 'computed', 'llm_suggested', 'community'))");
        DB::statement("ALTER TABLE ingredients ADD CONSTRAINT ingredients_confidence_check CHECK (confidence IN ('high', 'medium', 'low', 'unknown'))");
        // R7 — mapping không 'high' thì bắt buộc có mapping_note (DATA-MODEL §6).
        DB::statement("ALTER TABLE ingredients ADD CONSTRAINT ingredients_r7_mapping_note CHECK (mapping_confidence = 'high' OR mapping_note IS NOT NULL)");
        // V13 dưới dạng ràng buộc cứng: có density thì phải ghi rõ đo hay suy ra.
        DB::statement('ALTER TABLE ingredients ADD CONSTRAINT ingredients_density_note_check CHECK (density_g_per_ml IS NULL OR density_note IS NOT NULL)');
        // Index trigram để cảnh báo trùng tên khi nhập liệu (SETUP-003).
        DB::statement('CREATE INDEX ingredients_name_vi_trgm ON ingredients USING gin (name_vi gin_trgm_ops)');
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
