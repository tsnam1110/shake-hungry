<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * TCM-001c — chính sách P-DERIVE: nhãn SUY DIỄN cho dạng chế biến cơ học
 * đơn thành phần (quyết định của phiên chính theo uỷ quyền của chủ dự án,
 * xử lý điểm nghẽn của TCM-001b: nhóm bột/sợi không thể gieo trung thực từ
 * mục hạt gốc bằng dòng nhãn nguồn thật).
 *
 * Một nguyên liệu là DẠNG CHẾ BIẾN CƠ HỌC ĐƠN THÀNH PHẦN (xay thành bột,
 * ngâm nở thành sợi/bánh, sấy khô, ép) của đúng MỘT nguyên liệu gốc đã có
 * nhãn nguồn thật được mang nhãn suy diễn từ nhãn gốc đó. Cột mới:
 *  - `derived_from`: slug của nguyên liệu gốc (định danh ổn định ở tầng
 *    file — cùng cách dự án tham chiếu slug khắp data/). NULL = dòng nhãn
 *    nguồn thật.
 *
 * Kỷ luật P-DERIVE (kiểm ở V20 tầng file, chốt cứng một phần ở DB):
 *  - confidence của dòng suy diễn TRẦN 'low' — chốt ở CHECK dưới đây;
 *  - dòng suy diễn phải GƯƠNG đúng một dòng nhãn nguồn thật của nguyên
 *    liệu gốc (nature/flavor/element/element_basis khớp nguyên vẹn);
 *  - CẤM suy diễn chuỗi: nguyên liệu gốc phải có dòng nguồn thật — dòng
 *    suy diễn của gốc không làm căn cứ được;
 *  - CẤM cho nguyên liệu đa thành phần/hỗn hợp (có `components`) và cho
 *    nguyên liệu không định danh.
 * `source_ref` của dòng suy diễn trích lại nguồn của nhãn gốc kèm cụm
 * "P-DERIVE" — V20 bắt buộc có cụm này để mọi truy vết đều thấy rõ đây
 * không phải nhãn nguồn trực tiếp.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingredient_tcm_traits', function (Blueprint $table) {
            $table->string('derived_from', 128)->nullable();
        });
        DB::statement("ALTER TABLE ingredient_tcm_traits ADD CONSTRAINT tcm_derived_confidence_check CHECK (derived_from IS NULL OR confidence = 'low')");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE ingredient_tcm_traits DROP CONSTRAINT tcm_derived_confidence_check');
        Schema::table('ingredient_tcm_traits', function (Blueprint $table) {
            $table->dropColumn('derived_from');
        });
    }
};
