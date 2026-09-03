<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Dấu vết duyệt cho nguyên liệu và món (ING-004).
 *
 * Trước migration này `needs_review` là cờ một chiều: bật được, tắt được, nhưng
 * KHÔNG có chỗ nào ghi ai đã duyệt và duyệt lúc nào. Hệ quả thực tế phát hiện
 * ngày 2026-09-01: cả 255/255 file nguyên liệu đều `needs_review = true`, kể cả
 * 150 file của ING-001/ING-002 đã rà soát tay — vì không có chỗ lưu kết quả rà
 * soát nên không ai hạ cờ được. QT-1 đòi "phải được người duyệt trước khi hiển
 * thị ra ngoài" mà lại không có bằng chứng duyệt là một lỗ hổng, không phải chi tiết.
 *
 * Ràng buộc R10: chỉ được hạ `needs_review` khi đã ghi ĐỦ người duyệt và thời điểm.
 * Đây là ràng buộc cứng ở tầng database, cùng kiểu với R7 (mapping_note) và V13
 * (density_note) — cờ không thể bị tắt lặng lẽ bằng một lần sửa file.
 */
return new class extends Migration
{
    /** @var list<string> */
    private array $tables = ['ingredients', 'dishes'];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                // Người duyệt: ghi định danh người thật (email/handle), KHÔNG phải
                // tên agent. Dữ liệu do máy sinh không tự duyệt được chính nó (QT-1).
                $t->text('reviewed_by')->nullable();
                $t->timestampTz('reviewed_at')->nullable();
                $t->text('review_note')->nullable();
            });

            // Mặc định của `needs_review` vốn là FALSE — nghĩa là mọi bản ghi tạo
            // bằng đường khác seeder (Filament ở GĐ2, form đóng góp CTR-001, script
            // nhập liệu) mặc nhiên coi như "đã duyệt". Đúng ngược với QT-1. Đảo lại
            // thành TRUE: chưa duyệt là trạng thái mặc định, muốn ra khỏi nó thì
            // phải khai báo tường minh và để lại dấu vết.
            DB::statement("ALTER TABLE {$table} ALTER COLUMN needs_review SET DEFAULT true");

            // R10 — hạ cờ needs_review phải kèm bằng chứng ai/khi nào.
            DB::statement(
                "ALTER TABLE {$table} ADD CONSTRAINT {$table}_r10_review_trail CHECK ".
                '(needs_review = true OR (reviewed_by IS NOT NULL AND reviewed_at IS NOT NULL))'
            );
            // Ngược lại: đã ghi người duyệt thì phải ghi thời điểm, và ngược lại —
            // nửa bộ dấu vết không truy nguyên được.
            DB::statement(
                "ALTER TABLE {$table} ADD CONSTRAINT {$table}_review_pair_check CHECK ".
                '((reviewed_by IS NULL) = (reviewed_at IS NULL))'
            );
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$table}_r10_review_trail");
            DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$table}_review_pair_check");
            DB::statement("ALTER TABLE {$table} ALTER COLUMN needs_review SET DEFAULT false");
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn(['reviewed_by', 'reviewed_at', 'review_note']);
            });
        }
    }
};
