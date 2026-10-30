<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Chuẩn bị khả năng tìm kiếm theo nền tảng CSDL.
 *
 * Lịch sử: bản đầu (2026-08-31) bật pg_trgm + unaccent (+ pgvector tuỳ chọn)
 * trên PostgreSQL — ARCHITECTURE §4 bản cũ.
 *
 * Từ 2026-10-30 dự án chuyển sang MySQL 8 theo quyết định của chủ dự án
 * (xem ARCHITECTURE §4 — mục "Thay đổi 2026-10-30"):
 * - Tìm kiếm không dấu / sắp xếp tiếng Việt: dựa vào collation
 *   utf8mb4_vi_0900_ai_ci của database, không cần extension.
 * - Độ giống trigram (trước là pg_trgm): tính ở tầng PHP trong
 *   LacdoiValidateData (V7), không còn phụ thuộc CSDL.
 * - pgvector: MySQL 8 chưa có kiểu VECTOR (MySQL 9 mới có) — cột embedding
 *   tiếp tục hoãn tới GĐ4 như kế hoạch cũ.
 * Migration này giữ nhánh PostgreSQL để lịch sử có thể đối chiếu, nhưng
 * đường chạy chính thức hiện tại là MySQL.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            // MySQL (và nền tảng khác): không có extension tương đương cần bật.
            return;
        }

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
