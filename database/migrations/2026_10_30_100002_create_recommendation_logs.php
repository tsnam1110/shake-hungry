<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Nhật ký mỗi lần Lắc (REC-001 mục 5): seed để dựng lại được tình huống cũ. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recommendation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('slot', 8);
            $table->string('seed', 64);
            $table->foreignId('dish_id')->nullable()->constrained('dishes')->nullOnDelete();
            $table->smallInteger('relaxed_level')->default(0);
            $table->json('context')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendation_logs');
    }
};
