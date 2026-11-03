<?php

declare(strict_types=1);

namespace App\Nutrition;

/**
 * Phân tầng nguồn bảng thành phần (DATA-PIPELINE §7.2).
 *
 * Tầng THỨ CẤP (không ưu tiên) — theo quyết định chủ dự án 2026-10-31: các nguồn
 * từng bị hạn chế được mở lại để bổ sung cho nguyên liệu mà nguồn chính thống
 * (USDA, Ciqual, CNF, nhãn nhà sản xuất) không có. Mọi dữ liệu thuộc tầng này
 * đều bị coi là CHƯA KIỂM ĐỊNH: DishNutritionService ghi lại nguyên liệu nào
 * trong món dùng nguồn thứ cấp, và giao diện hiển thị cảnh báo cho người dùng.
 *
 * Đây là nơi DUY NHẤT định nghĩa tập nguồn thứ cấp — mọi nơi cần phân tầng
 * (engine, API, kiểm tra dữ liệu) đều hỏi qua đây để không lệch nhau.
 *
 * Bổ sung 2026-11-03 (chủ dự án duyệt nhập 6 proxy mức thấp): mã `proxy` dùng
 * cho proxy mà mã nguồn thật (nhãn thương mại qua Open Food Facts, trang dẫn
 * lại USDA, mục USDA chung cho cả nhóm loài) không thuộc tầng thứ cấp, nhưng
 * bản thân nó vẫn là số mượn, chưa kiểm định — gán mã thật sẽ làm mất cờ
 * cảnh báo, gán mã thứ cấp khác sẽ nói dối về nguồn. `proxy` không phải một
 * bảng thành phần: `mapping_note` của từng nguyên liệu phải ghi rõ proxy từ
 * đâu, khoảng nào, và còn thiếu gì để thay bằng nguồn chính thống.
 */
final class SourceTier
{
    /** @var list<string> */
    public const SECONDARY = [
        'vn_nin',      // Bảng thành phần thực phẩm Việt Nam (Viện Dinh dưỡng) — qua bản gốc/bản đăng lại
        'literature',  // Bài báo khoa học / tài liệu chuyên ngành có phân tích thành phần
        'mext_jp',     // Bảng thành phần Nhật Bản (MEXT) — truy cập qua nguồn đăng lại
        'ifct_in',     // Indian Food Composition Tables (ICMR–NIN) — qua nguồn đăng lại
        'thai_fct',    // Bảng thành phần Thái Lan — dự phòng, qua nguồn đăng lại
        'fnri_ph',     // FNRI Philippines — dự phòng, qua nguồn đăng lại
        'tbca_br',     // TBCA Brazil (USP/FoRC) — bảng quốc gia của nước bản địa của loài, FRUIT-002 2026-10-31
        'tkpi_id',     // TKPI Indonesia (Kemenkes) — bảng quốc gia của nước bản địa của loài, FRUIT-002 2026-10-31
        'proxy',       // Proxy mức thấp đã duyệt 2026-11-03 (không phải một bảng): xem ghi chú trên
    ];

    public static function isSecondary(?string $fctSource): bool
    {
        return $fctSource !== null && in_array($fctSource, self::SECONDARY, true);
    }
}
