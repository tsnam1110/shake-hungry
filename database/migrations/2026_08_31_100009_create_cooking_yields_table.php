<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * DATA-004 — bảng hệ số hao hụt khi nấu, nhập từ USDA Table of Cooking Yields
     * for Meat and Poultry, Release 2 (public domain). Khoá tra cứu:
     * (ndb_number → fdc_id của SR Legacy, preparation_method).
     */
    public function up(): void
    {
        Schema::create('cooking_yields', function (Blueprint $table) {
            $table->id();
            $table->string('fct_source', 32)->default('usda_cooking_yields_r2');
            $table->string('ndb_number', 10);
            $table->string('external_id', 64)->nullable(); // fdc_id SR Legacy tương ứng, null nếu NDB không còn trong SR Legacy
            $table->text('food_description');
            $table->string('preparation_method', 64);
            $table->decimal('yield_pct', 6, 2);
            $table->decimal('yield_min_pct', 6, 2)->nullable();
            $table->decimal('yield_max_pct', 6, 2)->nullable();
            $table->decimal('moisture_change_pct', 6, 2)->nullable();
            $table->decimal('fat_change_pct', 6, 2)->nullable();
            $table->smallInteger('sample_n')->nullable();
            $table->smallInteger('release_year')->nullable();
            $table->timestampTz('imported_at');

            $table->index(['ndb_number', 'preparation_method']);
            $table->index('external_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cooking_yields');
    }
};
