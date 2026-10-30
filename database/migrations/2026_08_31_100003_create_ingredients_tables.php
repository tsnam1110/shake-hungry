<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL không nhận default dạng literal cho cột JSON — phải dùng biểu thức.
        $jsonArrayDefault = DB::connection()->getDriverName() === 'pgsql'
            ? '[]' : new Expression('(JSON_ARRAY())');

        Schema::create('ingredients', function (Blueprint $table) use ($jsonArrayDefault) {
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
            // DATA-004: hệ số suy từ bảng USDA Cooking Yields R2 — bắt buộc ghi
            // suy từ dòng nào (NDB + phương pháp), cùng logic với density_note.
            $table->text('default_yield_factor_note')->nullable();
            $table->decimal('oil_absorption_pct', 5, 2)->nullable();
            // DATA-008: % phần ăn được của khối lượng mua ở chợ (cả xương/vỏ).
            // null = bán sẵn phần ăn được, hoặc chưa tra được (ghi rõ ở note).
            $table->decimal('edible_portion_pct', 5, 2)->nullable();
            $table->text('edible_portion_note')->nullable();
            $table->boolean('is_ready_to_eat')->default(false);
            $table->text('density_note')->nullable();
            $table->text('source_url')->nullable();
            $table->text('scientific_name')->nullable();
            $table->jsonb('nutrition_snapshot')->nullable();
            $table->jsonb('alternatives')->default($jsonArrayDefault);
            $table->jsonb('secondary_sources')->default($jsonArrayDefault);
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
        // DATA-004: hệ số hao hụt phải ghi rõ suy từ dòng nào.
        DB::statement('ALTER TABLE ingredients ADD CONSTRAINT ingredients_yield_note_check CHECK (default_yield_factor IS NULL OR default_yield_factor_note IS NOT NULL)');
        // DATA-008: hệ số phần ăn được phải có nguồn (foodMeasures hoặc phép cân) — cùng logic V13.
        DB::statement('ALTER TABLE ingredients ADD CONSTRAINT ingredients_edible_note_check CHECK (edible_portion_pct IS NULL OR edible_portion_note IS NOT NULL)');
        DB::statement('ALTER TABLE ingredients ADD CONSTRAINT ingredients_edible_range_check CHECK (edible_portion_pct IS NULL OR (edible_portion_pct > 0 AND edible_portion_pct <= 100))');
        // Index trigram để cảnh báo trùng tên khi nhập liệu (SETUP-003).
        // Chỉ mục tìm kiếm theo tên: PostgreSQL dùng GIN trigram (pg_trgm);
        // MySQL 8 (nền tảng chính từ 2026-10-30) dùng FULLTEXT ngram.
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX ingredients_name_vi_trgm ON ingredients USING gin (name_vi gin_trgm_ops)');
        } else {
            DB::statement('CREATE FULLTEXT INDEX ingredients_name_vi_ft ON ingredients (name_vi) WITH PARSER ngram');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
