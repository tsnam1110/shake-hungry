<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Yêu thích + phản hồi cho nước uống (Phần 3B, đồng nhất với món/quả):
 * thêm drink_id vào favorites (một người dùng chỉ tim một đồ uống một lần)
 * và feedback_tickets (ticket gắn được vào đồ uống, nullOnDelete như món/quả).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('favorites', function (Blueprint $table) {
            $table->foreignId('drink_id')->nullable()->after('fruit_id')->constrained('drinks')->cascadeOnDelete();
            $table->unique(['user_id', 'drink_id']);
        });

        Schema::table('feedback_tickets', function (Blueprint $table) {
            $table->foreignId('drink_id')->nullable()->after('ingredient_id')->constrained('drinks')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('feedback_tickets', function (Blueprint $table) {
            $table->dropForeign(['drink_id']);
            $table->dropColumn('drink_id');
        });

        Schema::table('favorites', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'drink_id']);
            $table->dropForeign(['drink_id']);
            $table->dropColumn('drink_id');
        });
    }
};
