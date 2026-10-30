<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Chuẩn hoá tên tiếng Việt + độ giống trigram — dùng chung cho
 * lacdoi:validate-data (V7) và cảnh báo trùng món trong trang quản trị (ADM-005).
 * Một thuật toán duy nhất để hai nơi không lệch nhau.
 */
class NameSimilarity
{
    /** Chữ thường + bỏ dấu tiếng Việt (đ/Đ thay tay vì Normalizer không tách được). */
    public static function normalize(string $name): string
    {
        $name = str_replace(['đ', 'Đ'], ['d', 'D'], $name);
        $name = mb_strtolower($name, 'UTF-8');
        if (class_exists(\Normalizer::class)) {
            $decomposed = \Normalizer::normalize($name, \Normalizer::FORM_D);
            if ($decomposed !== false) {
                $name = preg_replace('/\p{Mn}+/u', '', $decomposed) ?? $name;
            }
        }

        return trim(preg_replace('/\s+/u', ' ', $name) ?? $name);
    }

    /**
     * Độ giống trigram: mỗi từ đệm hai khoảng trắng đầu + một cuối, tách
     * trigram, similarity = |giao| / (|A| + |B| − |giao|). Trả 0.0 – 1.0.
     */
    public static function similarity(string $a, string $b): float
    {
        $setA = self::trigramSet(self::normalize($a));
        $setB = self::trigramSet(self::normalize($b));
        if ($setA === [] || $setB === []) {
            return 0.0;
        }
        $shared = count(array_intersect_key($setA, $setB));

        return $shared / (count($setA) + count($setB) - $shared);
    }

    /** @return array<string, true> */
    private static function trigramSet(string $text): array
    {
        $set = [];
        foreach (preg_split('/\s+/u', $text) ?: [] as $word) {
            if ($word === '') {
                continue;
            }
            $padded = '  '.$word.' ';
            $length = mb_strlen($padded, 'UTF-8');
            for ($i = 0; $i + 3 <= $length; $i++) {
                $set[mb_substr($padded, $i, 3, 'UTF-8')] = true;
            }
        }

        return $set;
    }
}
