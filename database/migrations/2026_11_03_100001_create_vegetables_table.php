<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vegetables', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name_vi');
            $table->foreignId('ingredient_id')->nullable()->constrained()->nullOnDelete();
            $table->string('group'); // rau-la | rau-cu | rau-qua | dau-do | rau-thom-an-kem | dang-che-bien
            $table->string('supply'); // trong-nuoc | da-lat | nhap-khau | ca-hai
            $table->string('state'); // tuoi | muoi | kho | chua
            $table->json('months_bac')->nullable();
            $table->json('months_trung')->nullable();
            $table->json('months_nam')->nullable();
            $table->json('region_notes')->nullable();
            $table->json('main_season_months')->nullable();
            $table->json('best_months')->nullable();
            $table->decimal('kcal_per_100g', 8, 2)->nullable();
            $table->string('kcal_confidence')->nullable();
            $table->json('aliases')->nullable();
            $table->text('selection_note')->nullable();
            $table->text('storage_note')->nullable();
            $table->text('cooking_note')->nullable();
            $table->text('safety_note')->nullable();
            $table->boolean('uncertain')->default(false);
            $table->text('uncertain_note')->nullable();
            $table->string('fresh_counterpart_slug')->nullable();
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
        Schema::dropIfExists('vegetables');
    }
};
