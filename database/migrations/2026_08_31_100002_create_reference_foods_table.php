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
        Schema::create('reference_foods', function (Blueprint $table) {
            $table->id();
            $table->string('fct_source', 32);
            $table->string('external_id', 64);
            $table->text('name_en');
            $table->text('name_vi')->nullable();
            $table->decimal('kcal_per_100g', 8, 2)->nullable();
            $table->decimal('protein_g', 8, 2)->nullable();
            $table->decimal('fat_g', 8, 2)->nullable();
            $table->decimal('carb_g', 8, 2)->nullable();
            $table->decimal('fiber_g', 8, 2)->nullable();
            $table->jsonb('micronutrients')->nullable();
            $table->jsonb('raw_payload')->nullable();
            $table->timestampTz('imported_at');

            $table->unique(['fct_source', 'external_id']);
        });

        // Danh sách nguồn hợp lệ: DATA-MODEL §2 + DATA-REPOSITORY §3 (khối reference_food).
        DB::statement("ALTER TABLE reference_foods ADD CONSTRAINT reference_foods_fct_source_check CHECK (fct_source IN ('usda_foundation', 'usda_sr_legacy', 'usda_fndds', 'usda_branded', 'usda_survey', 'vn_nin', 'ciqual', 'cnf', 'uk_cofid', 'off', 'nhan_san_pham', 'tu_do'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('reference_foods');
    }
};
