<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * TCM-001a — `element_basis` cho dòng nhãn tính vị (quyết định của phiên
 * chính theo uỷ quyền của chủ dự án, xử lý điểm diễn giải số 1 trong
 * TCM-000.md):
 *
 * Một nguồn có thể trình bày ánh xạ hành theo ba cách khác nhau, và dòng
 * nhãn phải nói rõ element của nó đứng trên căn cứ nào:
 *  - 'flavor' (mặc định): hành suy từ VỊ theo ánh xạ cổ điển
 *    (toan→Mộc, kho→Hoả, cam→Thổ, tan→Kim, ham→Thuỷ) — V20 kiểm element
 *    ghi tường minh phải khớp ánh xạ này, như TCM-000 đã chốt.
 *  - 'nature': nguồn trình bày hành theo TÍNH (trình bày Việt Nam trong
 *    NGU-HANH-AM-DUONG §3: hàn→Thuỷ, lương→Kim, bình→Thổ, ôn→Mộc,
 *    nhiệt→Hoả) — element ghi như nguồn ghi, V20 kiểm khớp ánh xạ theo tính.
 *  - 'direct': nguồn ghi hành trực tiếp, không qua vị hay tính — element
 *    ghi nguyên văn, V20 chỉ kiểm từ vựng.
 *
 * Chỉ dòng basis='flavor' tham gia bỏ phiếu `dominant_element` khi tổng
 * hợp món (TcmAggregator) — trộn hai hệ ánh xạ vào một cuộc bỏ phiếu sẽ
 * cho kết quả không truy vết được về một hệ quy ước nào.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingredient_tcm_traits', function (Blueprint $table) {
            $table->string('element_basis', 8)->default('flavor');
        });
        DB::statement("ALTER TABLE ingredient_tcm_traits ADD CONSTRAINT tcm_element_basis_check CHECK (element_basis IN ('flavor', 'nature', 'direct'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE ingredient_tcm_traits DROP CONSTRAINT tcm_element_basis_check');
        Schema::table('ingredient_tcm_traits', function (Blueprint $table) {
            $table->dropColumn('element_basis');
        });
    }
};
