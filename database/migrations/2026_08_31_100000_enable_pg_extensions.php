<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // pg_trgm + unaccent bắt buộc (ARCHITECTURE §4). pgvector tuỳ chọn ở GĐ1 —
        // chỉ bật khi đã cài, xem SETUP-001 "Cạm bẫy đã biết".
        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');
        DB::statement('CREATE EXTENSION IF NOT EXISTS unaccent');

        $vectorAvailable = DB::selectOne(
            "SELECT count(*) AS n FROM pg_available_extensions WHERE name = 'vector'"
        );
        if ((int) $vectorAvailable->n > 0) {
            DB::statement('CREATE EXTENSION IF NOT EXISTS vector');
        }
    }

    public function down(): void
    {
        // Không gỡ extension — database khác có thể đang dùng.
    }
};
