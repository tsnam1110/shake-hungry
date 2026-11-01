<?php

declare(strict_types=1);

namespace App\Cultural;

/**
 * Từ vựng + quy ước của lớp tính vị (QT-6, NGU-HANH-AM-DUONG.md).
 *
 * Hai loại hằng số phải phân biệt rõ:
 *  - TỪ VỰNG CỔ ĐIỂN (tứ khí, ngũ vị, ngũ hành, ánh xạ vị → hành): ổn định
 *    qua mọi trường phái, là phần được chuẩn hoá của hệ phân loại.
 *  - QUY ƯỚC CỦA DỰ ÁN (điểm tính, hệ số chế biến, ánh xạ các mã cooking_method
 *    ngoài nhóm gốc, ngưỡng hiển thị): y văn KHÔNG định lượng các con số này.
 *    Chúng tồn tại để tổng hợp được bằng máy và PHẢI được ghi rõ là quy ước
 *    ở mọi nơi hiển thị — không trình bày như phép tính khoa học.
 */
final class TcmVocabulary
{
    /** Điểm tính theo tứ khí — quy ước dự án (thang đối xứng −2…+2). */
    public const NATURE_POINTS = [
        'han' => -2.0,
        'luong' => -1.0,
        'binh' => 0.0,
        'on' => 1.0,
        'nhiet' => 2.0,
    ];

    public const NATURE_LABELS = [
        'han' => 'Hàn',
        'luong' => 'Lương',
        'binh' => 'Bình',
        'on' => 'Ôn',
        'nhiet' => 'Nhiệt',
    ];

    public const FLAVOR_LABELS = [
        'toan' => 'Toan (chua)',
        'kho' => 'Khổ (đắng)',
        'cam' => 'Cam (ngọt)',
        'tan' => 'Tân (cay)',
        'ham' => 'Hàm (mặn)',
        'dam' => 'Đạm (nhạt)',
    ];

    public const ELEMENT_LABELS = [
        'moc' => 'Mộc',
        'hoa' => 'Hoả',
        'tho' => 'Thổ',
        'kim' => 'Kim',
        'thuy' => 'Thuỷ',
    ];

    /**
     * Ánh xạ cổ điển vị → hành. `dam` (nhạt) nằm ngoài ngũ vị chuẩn nên
     * không có hành suy ra — nhãn ghi element tường minh mới được dùng.
     */
    public const ELEMENT_BY_FLAVOR = [
        'toan' => 'moc',
        'kho' => 'hoa',
        'cam' => 'tho',
        'tan' => 'kim',
        'ham' => 'thuy',
        'dam' => null,
    ];

    /**
     * Ánh xạ hành theo TÍNH — cách trình bày trong văn hoá Việt
     * (NGU-HANH-AM-DUONG §3, theo Trần Ngọc Thêm): năm mức âm dương ánh xạ
     * thẳng vào ngũ hành. Đây là một hệ ánh xạ KHÁC với ánh xạ theo vị;
     * dòng nhãn phải khai `element_basis` để biết element của nó thuộc hệ
     * nào (TCM-001a).
     */
    public const ELEMENT_BY_NATURE = [
        'han' => 'thuy',
        'luong' => 'kim',
        'binh' => 'tho',
        'on' => 'moc',
        'nhiet' => 'hoa',
    ];

    /**
     * Căn cứ của `element` trong một dòng nhãn (TCM-001a):
     *  - 'flavor': suy từ vị (mặc định) — hệ duy nhất tham gia bỏ phiếu
     *    dominant_element khi tổng hợp món.
     *  - 'nature': nguồn trình bày hành theo tính.
     *  - 'direct': nguồn ghi hành trực tiếp, không qua vị/tính.
     */
    public const ELEMENT_BASES = ['flavor', 'nature', 'direct'];

    /**
     * Hệ số điều chỉnh theo cách chế biến — QUY ƯỚC CỦA DỰ ÁN.
     * Nhóm gốc theo tài liệu thiết kế: chiên/nướng/kho +0,5 · xào +0,25 ·
     * luộc/hấp −0,25 · sống −0,5. Ánh xạ các mã còn lại của dự án:
     * `ran` và `chien-ngap-dau` thuộc nhóm chiên; `nau` là nấu trong nước
     * (canh/súp) nên thuộc nhóm luộc/hấp; `tron` chủ yếu là thành phần sống
     * trộn không qua nhiệt nên thuộc nhóm sống. Mã lạ/null ⇒ 0.
     */
    public const COOKING_ADJUSTMENTS = [
        'chien' => 0.5,
        'chien-ngap-dau' => 0.5,
        'ran' => 0.5,
        'nuong' => 0.5,
        'kho' => 0.5,
        'xao' => 0.25,
        'luoc' => -0.25,
        'hap' => -0.25,
        'nau' => -0.25,
        'song' => -0.5,
        'tron' => -0.5,
    ];

    /** Ngưỡng trung thực khi hiển thị (kế hoạch Mức 3 §1) — không đổi theo quyết định mặc định bật. */
    public const DISPLAY_MIN_COVERAGE = 30.0;

    public const FULL_DISPLAY_COVERAGE = 60.0;

    /** Cổng Mức 3 và cổng đường nâng bù (kế hoạch Mức 3 §1). */
    public const LEVEL3_MIN_COVERAGE = 60.0;

    public static function elementForFlavor(?string $flavor): ?string
    {
        if ($flavor === null) {
            return null;
        }

        return self::ELEMENT_BY_FLAVOR[$flavor] ?? null;
    }

    public static function elementForNature(?string $nature): ?string
    {
        if ($nature === null) {
            return null;
        }

        return self::ELEMENT_BY_NATURE[$nature] ?? null;
    }

    public static function cookingAdjustment(?string $method): float
    {
        if ($method === null) {
            return 0.0;
        }

        return self::COOKING_ADJUSTMENTS[$method] ?? 0.0;
    }

    /** Đảo điểm tổng hợp về nhãn tính gần nhất để hiển thị. */
    public static function natureForScore(float $score): string
    {
        return match (true) {
            $score <= -1.5 => 'han',
            $score <= -0.5 => 'luong',
            $score < 0.5 => 'binh',
            $score < 1.5 => 'on',
            default => 'nhiet',
        };
    }
}
