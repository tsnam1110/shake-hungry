<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drinks', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name_vi');
            $table->string('drink_group');
            $table->string('kind'); // pha-che | dong-goi
            $table->string('temperature')->nullable(); // nong | lanh | ca-hai
            $table->string('glass')->nullable();
            $table->integer('serving_ml')->nullable();
            $table->string('technique')->nullable();
            $table->string('ice')->nullable();
            $table->json('garnish')->nullable();
            $table->json('ingredients')->nullable();
            $table->json('recipe_steps')->nullable();
            $table->decimal('sugar_added_g', 8, 2)->nullable();
            $table->text('sugar_note')->nullable();
            $table->string('caffeine_level')->nullable(); // khong | thap | trung-binh | cao
            $table->boolean('alcohol')->default(false);
            $table->decimal('alcohol_pct_estimate', 5, 2)->nullable();
            $table->text('alcohol_note')->nullable();
            $table->json('safety_flags')->nullable();
            $table->string('difficulty')->nullable();
            $table->integer('prep_minutes')->nullable();
            $table->string('best_occasion')->nullable();
            $table->json('food_pairing')->nullable();
            $table->string('packaging')->nullable();
            $table->json('sugar_range_g')->nullable();
            $table->json('alcohol_pct_range')->nullable();
            $table->text('label_note')->nullable();
            $table->json('brand_examples')->nullable();
            $table->text('cultural_note')->nullable();
            $table->json('recipe_sources')->nullable();
            $table->string('mocktail_counterpart_slug')->nullable();
            $table->boolean('uncertain')->default(false);
            $table->text('uncertain_note')->nullable();
            $table->text('source_note')->nullable();
            $table->boolean('needs_review')->default(true);
            $table->string('reviewed_by')->nullable();
            $table->date('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drinks');
    }
};
