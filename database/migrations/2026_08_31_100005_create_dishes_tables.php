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
        Schema::create('dishes', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 96)->unique();
            $table->foreignId('parent_dish_id')->nullable()->constrained('dishes')->restrictOnDelete();
            $table->char('country_code', 2);
            $table->foreignId('region_id')->nullable()->constrained('regions')->restrictOnDelete();
            $table->boolean('is_eat_out')->default(false);
            $table->boolean('is_home_cook')->default(false);
            $table->string('cooking_method', 24)->nullable();
            $table->smallInteger('completeness_level')->default(1);
            $table->jsonb('recipe_steps')->nullable();
            $table->text('serving_desc')->nullable();
            $table->decimal('serving_grams', 8, 2)->nullable();
            $table->jsonb('estimated_kcal')->nullable();
            $table->string('status', 16)->default('draft');
            $table->string('source', 16);
            $table->string('confidence', 8);
            $table->boolean('needs_review')->default(false);
            $table->timestampsTz();
        });

        // Danh sách cách chế biến: DATA-MODEL §4 + CALORIE-ENGINE §4 (chien/ran/chien-ngap-dau).
        DB::statement("ALTER TABLE dishes ADD CONSTRAINT dishes_cooking_method_check CHECK (cooking_method IS NULL OR cooking_method IN ('luoc', 'hap', 'xao', 'chien', 'ran', 'chien-ngap-dau', 'nuong', 'kho', 'nau', 'song', 'tron'))");
        DB::statement('ALTER TABLE dishes ADD CONSTRAINT dishes_completeness_check CHECK (completeness_level BETWEEN 1 AND 3)');
        DB::statement("ALTER TABLE dishes ADD CONSTRAINT dishes_status_check CHECK (status IN ('draft', 'published', 'archived'))");
        DB::statement("ALTER TABLE dishes ADD CONSTRAINT dishes_source_check CHECK (source IN ('imported', 'curated', 'computed', 'llm_suggested', 'community'))");
        DB::statement("ALTER TABLE dishes ADD CONSTRAINT dishes_confidence_check CHECK (confidence IN ('high', 'medium', 'low', 'unknown'))");
        DB::statement('CREATE INDEX dishes_slug_trgm ON dishes USING gin (slug gin_trgm_ops)');

        // Cột embedding vector(768) chỉ tạo được khi pgvector đã cài (SETUP-003 "Cạm bẫy").
        // Chưa cài ⇒ bỏ qua CÓ Ý THỨC, ghi ở SETUP-001/SETUP-003 mục Ghi chú; GĐ4 (REC-002) mới cần.
        $hasVector = DB::selectOne("SELECT count(*) AS n FROM pg_extension WHERE extname = 'vector'");
        if ((int) $hasVector->n > 0) {
            DB::statement('ALTER TABLE dishes ADD COLUMN embedding vector(768)');
        }

        Schema::create('dish_names', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dish_id')->constrained('dishes')->cascadeOnDelete();
            $table->string('locale', 8);
            $table->text('name');
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_alias')->default(false);
        });

        // Mỗi (dish, locale) đúng một dòng primary (DATA-MODEL §4).
        DB::statement('CREATE UNIQUE INDEX dish_names_one_primary ON dish_names (dish_id, locale) WHERE is_primary');
        DB::statement('CREATE INDEX dish_names_name_trgm ON dish_names USING gin (name gin_trgm_ops)');

        Schema::create('dish_meal_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dish_id')->constrained('dishes')->cascadeOnDelete();
            $table->string('slot', 8);
            $table->smallInteger('weight');

            $table->unique(['dish_id', 'slot']);
        });

        DB::statement("ALTER TABLE dish_meal_slots ADD CONSTRAINT dish_meal_slots_slot_check CHECK (slot IN ('sang', 'trua', 'chieu', 'toi', 'an-vat'))");
        DB::statement('ALTER TABLE dish_meal_slots ADD CONSTRAINT dish_meal_slots_weight_check CHECK (weight BETWEEN 1 AND 10)');

        Schema::create('dish_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dish_id')->constrained('dishes')->cascadeOnDelete();
            // R9 — khoá ngoại RESTRICT: không trỏ được tới nguyên liệu không tồn tại,
            // và không xoá được nguyên liệu đang được món dùng (DATA-MODEL §6).
            $table->foreignId('ingredient_id')->constrained('ingredients')->restrictOnDelete();
            $table->decimal('quantity', 8, 2);
            $table->string('unit_name', 64);
            $table->decimal('grams_resolved', 8, 2)->nullable();
            $table->boolean('is_optional')->default(false);
            $table->boolean('is_discarded')->default(false);
            // DATA-008: mặc định quantity là KHỐI LƯỢNG MUA Ở CHỢ (cả xương/vỏ) và
            // engine áp edible_portion_pct; true = quantity đã là phần ăn được, không áp nữa.
            $table->boolean('is_edible_weight')->default(false);
            $table->text('note')->nullable();
            $table->string('source', 16);
            $table->string('confidence', 8)->nullable();
        });

        DB::statement("ALTER TABLE dish_ingredients ADD CONSTRAINT dish_ingredients_source_check CHECK (source IN ('imported', 'curated', 'computed', 'llm_suggested', 'community'))");

        Schema::create('dish_nutrition', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dish_id')->constrained('dishes')->cascadeOnDelete();
            $table->string('basis', 16);
            $table->decimal('kcal_min', 8, 2);
            $table->decimal('kcal_max', 8, 2);
            $table->decimal('protein_g', 8, 2)->nullable();
            $table->decimal('fat_g', 8, 2)->nullable();
            $table->decimal('carb_g', 8, 2)->nullable();
            $table->decimal('fiber_g', 8, 2)->nullable();
            $table->string('confidence', 8);
            $table->string('fct_source', 32);
            $table->jsonb('missing_ingredients')->default('[]');
            $table->timestampTz('computed_at');
            $table->string('engine_version', 16);
            $table->string('source', 16);
        });

        DB::statement("ALTER TABLE dish_nutrition ADD CONSTRAINT dish_nutrition_basis_check CHECK (basis IN ('per_serving', 'per_100g'))");
        // R1 — chốt chặn kỹ thuật cho QT-1 (CLAUDE.md §3): LLM không bao giờ được
        // ghi số dinh dưỡng, kể cả khi code có lỗi.
        DB::statement("ALTER TABLE dish_nutrition ADD CONSTRAINT dish_nutrition_r1_no_llm CHECK (source <> 'llm_suggested')");
        // R2 — luôn là khoảng hợp lệ (QT-5).
        DB::statement('ALTER TABLE dish_nutrition ADD CONSTRAINT dish_nutrition_r2_range CHECK (kcal_min <= kcal_max)');
        // R3 — thiếu nguyên liệu thì không được nhận confidence 'high' (QT-5).
        DB::statement("ALTER TABLE dish_nutrition ADD CONSTRAINT dish_nutrition_r3_missing CHECK (missing_ingredients = '[]'::jsonb OR confidence <> 'high')");

        Schema::create('dish_recipe_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dish_id')->constrained('dishes')->cascadeOnDelete();
            $table->text('url');
            $table->date('accessed_at');
            $table->text('note')->nullable();
        });

        Schema::create('dish_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dish_id')->constrained('dishes')->cascadeOnDelete();
            $table->text('path');
            // R4 — NOT NULL: ảnh không license không vào được database (QT-2).
            $table->string('license', 32);
            $table->text('attribution')->nullable();
            $table->boolean('is_primary')->default(false);
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 64)->unique();
            $table->text('name');
        });

        Schema::create('dish_tags', function (Blueprint $table) {
            $table->foreignId('dish_id')->constrained('dishes')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();

            $table->unique(['dish_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dish_tags');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('dish_media');
        Schema::dropIfExists('dish_recipe_sources');
        Schema::dropIfExists('dish_nutrition');
        Schema::dropIfExists('dish_ingredients');
        Schema::dropIfExists('dish_meal_slots');
        Schema::dropIfExists('dish_names');
        Schema::dropIfExists('dishes');
    }
};
