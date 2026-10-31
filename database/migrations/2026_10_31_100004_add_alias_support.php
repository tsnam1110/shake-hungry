<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tên gọi khác (tên vùng miền) — ALIAS-001.
 *
 * - Món ăn đã có sẵn cơ chế: dish_names.is_alias (DATA-MODEL §4). Migration này
 *   chỉ bổ sung cột `note` để ghi nhãn vùng miền ("miền Nam", "tên cũ"...).
 * - Nguyên liệu chưa có cơ chế tên phụ: thêm bảng ingredient_aliases mô phỏng
 *   dish_names (name + note), kèm FULLTEXT ngram để tìm kiếm như tên món.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dish_names', function (Blueprint $table) {
            $table->string('note', 255)->nullable()->after('is_alias');
        });

        Schema::create('ingredient_aliases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnDelete();
            $table->string('name', 255);
            $table->string('note', 255)->nullable();
        });

        DB::statement('CREATE FULLTEXT INDEX ingredient_aliases_name_ft ON ingredient_aliases (name) WITH PARSER ngram');
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredient_aliases');

        Schema::table('dish_names', function (Blueprint $table) {
            $table->dropColumn('note');
        });
    }
};
