<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredient_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnDelete();
            // DATA-MODEL ghi varchar(32) nhưng dữ liệu thật có tên đơn vị dài hơn
            // ("phần ăn được từ 1 lb gà nguyên con", 34 ký tự) — nới 64, đã đính chính (QT-7).
            $table->string('unit_name', 64);
            // DATA-MODEL ghi numeric(8,2) nhưng dữ liệu thật cần 3 chữ số lẻ
            // (hạt sen khô: 0,675 g/hạt) — đã đính chính tài liệu cùng commit (QT-7).
            $table->decimal('grams', 8, 3);
            $table->text('note')->nullable();
            // DATA-MODEL ghi varchar(16) nhưng dữ liệu thật chứa nguồn dài hơn
            // ("FDC 174277 foodMeasures") — dùng text, đã đính chính DATA-MODEL (QT-7).
            $table->text('source');
            $table->timestampsTz();

            $table->unique(['ingredient_id', 'unit_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredient_units');
    }
};
