<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Thumbnail cho thẻ danh sách (IMG-001, quy tắc 2026-11-03).
 *
 * Ảnh gốc trong data/media/ vẫn là nguồn sự thật duy nhất (QT-3) và vẫn
 * dùng cho trang chi tiết. Thumbnail KHÔNG phải dữ liệu mới: nó chỉ là
 * bản thu nhỏ suy ra từ ảnh gốc, sinh on-demand bằng GD và cache trong
 * storage/app/thumbnails/ (thư mục này đã bị .gitignore, không commit,
 * không vào MANIFEST, không qua validator V2/V19).
 *
 * Chất lượng cho cả PC và mobile (chủ dự án chốt 2026-11-03):
 * - Thẻ PC rộng nhất ~320 px (trang wide 980 px, lưới 3 cột); màn hình
 *   retina cần gấp đôi, tức ~640 px → bản 800w dư một khoảng an toàn.
 * - Thẻ mobile 2 cột rộng ~180 px; retina cần ~360 px → bản 400w vừa đủ.
 * - Trình duyệt chọn qua srcset + sizes, không phải một bản duy nhất
 *   cho cả hai: mobile không tải bản PC, PC retina không nhận bản mờ.
 * - Giữ tỉ lệ gốc (không crop, không kéo dãn; CSS object-fit: cover
 *   của thẻ lo phần cắt khung), không upscale (ảnh gốc nhỏ hơn bản
 *   yêu cầu thì dùng thẳng ảnh gốc), WebP quality 85.
 */
class Thumbnail
{
    /** Các chiều rộng được phép — ngoài danh sách này route trả 404. */
    public const WIDTHS = [400, 800];

    public const QUALITY = 85;

    /**
     * URL thumbnail từ đường dẫn media đã lưu, VD:
     * media/dishes/bun-bo-hue.webp + 800 → /media/thumb/800/dishes/bun-bo-hue.webp
     */
    public static function url(string $mediaPath, int $width): string
    {
        $rel = preg_replace('#^media/#', '', $mediaPath) ?? $mediaPath;

        return "/media/thumb/{$width}/{$rel}";
    }

    /** Chuỗi srcset cho thẻ danh sách: "…/400/… 400w, …/800/… 800w". */
    public static function srcset(string $mediaPath): string
    {
        return implode(', ', array_map(
            fn (int $w) => self::url($mediaPath, $w)." {$w}w",
            self::WIDTHS,
        ));
    }

    /**
     * File thumbnail đã cache cho ảnh gốc, sinh lại khi ảnh gốc mới hơn
     * file cache (ảnh gốc bị thay thì thumbnail không kẹt bản cũ).
     * Trả null khi không sinh được (thiếu GD, ảnh lỗi…) — controller khi
     * đó phục vụ thẳng ảnh gốc thay vì trả ảnh vỡ.
     */
    public static function cachedFile(string $originalPath, int $width): ?string
    {
        $rel = preg_replace('#^media/#', '', $originalPath) ?? $originalPath;
        $original = base_path('data/media/'.$rel);
        if (! is_file($original)) {
            return null;
        }

        $cached = storage_path('app/thumbnails/'.$width.'/'.$rel);
        if (is_file($cached) && filemtime($cached) !== false
            && filemtime($original) !== false
            && filemtime($cached) >= filemtime($original)) {
            return $cached;
        }

        $size = getimagesize($original);
        if ($size === false) {
            return null;
        }
        [$origW, $origH] = $size;

        // Không upscale: ảnh gốc đã nhỏ hơn bản yêu cầu thì dùng gốc.
        if ($origW <= $width) {
            return $original;
        }

        if (! function_exists('imagecreatefromwebp')) {
            return null;
        }

        $src = @imagecreatefromwebp($original);
        if ($src === false) {
            return null;
        }

        $newH = (int) round($origH * $width / $origW);
        $dst = imagecreatetruecolor($width, $newH);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $width, $newH, $origW, $origH);
        imagedestroy($src);

        $dir = dirname($cached);
        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            imagedestroy($dst);

            return null;
        }

        if (! imagewebp($dst, $cached, self::QUALITY)) {
            imagedestroy($dst);

            return null;
        }
        imagedestroy($dst);
        // Dấu thời gian của cache phải ≥ ảnh gốc để lần sau khỏi sinh lại.
        touch($cached, max(time(), (int) filemtime($original)));

        return $cached;
    }
}
