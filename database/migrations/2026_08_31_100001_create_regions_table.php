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
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('regions')->restrictOnDelete();
            $table->char('country_code', 2);
            $table->string('code', 32)->unique();
            $table->text('name');
            $table->string('level', 16);
            $table->timestampsTz();
        });

        DB::statement("ALTER TABLE regions ADD CONSTRAINT regions_level_check CHECK (level IN ('country', 'macro_region', 'province'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};
