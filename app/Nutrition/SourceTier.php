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
    ];

    public static function isSecondary(?string $fctSource): bool
    {
        return $fctSource !== null && in_array($fctSource, self::SECONDARY, true);
    }
}
