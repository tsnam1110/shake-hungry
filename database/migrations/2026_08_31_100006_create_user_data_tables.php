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

        Schema::create('user_profiles', function (Blueprint $table) use ($jsonArrayDefault) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->decimal('height_cm', 5, 1)->nullable();
            $table->decimal('weight_kg', 5, 1)->nullable();
            $table->smallInteger('birth_year')->nullable();
            // varchar(16): giá trị 'unspecified' dài 11 ký tự — PostgreSQL chấp
            // nhận default dài hơn cột (tự cắt khi ép kiểu), MySQL strict thì không.
            $table->string('sex', 16)->default('unspecified');
            $table->string('activity_level', 16)->nullable();
            $table->string('diet_type', 24)->nullable();
            $table->jsonb('excluded_ingredients')->default($jsonArrayDefault);
            $table->timestampsTz();
        });

        DB::statement("ALTER TABLE user_profiles ADD CONSTRAINT user_profiles_sex_check CHECK (sex IN ('male', 'female', 'unspecified'))");

        Schema::create('meal_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('dish_id')->constrained('dishes')->restrictOnDelete();
            $table->string('slot', 8);
            $table->timestampTz('eaten_at');
            $table->string('source', 16);
            $table->timestampsTz();
        });

        DB::statement("ALTER TABLE meal_logs ADD CONSTRAINT meal_logs_slot_check CHECK (slot IN ('sang', 'trua', 'chieu', 'toi', 'an-vat'))");
        DB::statement("ALTER TABLE meal_logs ADD CONSTRAINT meal_logs_source_check CHECK (source IN ('shake_accepted', 'manual'))");

        Schema::create('contributions', function (Blueprint $table) {
            $table->id();
            $table->string('type', 24);
            $table->jsonb('payload');
            $table->string('status', 16)->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('reviewed_at')->nullable();
            $table->text('reject_reason')->nullable();
            $table->timestampsTz();
        });

        DB::statement("ALTER TABLE contributions ADD CONSTRAINT contributions_type_check CHECK (type IN ('new_dish', 'fix_dish', 'new_place'))");
        DB::statement("ALTER TABLE contributions ADD CONSTRAINT contributions_status_check CHECK (status IN ('pending', 'approved', 'rejected'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('contributions');
        Schema::dropIfExists('meal_logs');
        Schema::dropIfExists('user_profiles');
    }
};
