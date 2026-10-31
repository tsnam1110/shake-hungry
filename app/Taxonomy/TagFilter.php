<?php

declare(strict_types=1);

namespace App\Taxonomy;

use Illuminate\Http\Request;

/**
 * Đọc tham số lọc nhãn từ request (TAXONOMY.md §7).
 *
 * PHP nuốt mất tham số lặp (`?tag=a&tag=b` chỉ còn giá trị cuối) nên phải tự
 * gom từ QUERY_STRING gốc: mọi `tag=` / `tag[]=` đều được nhận, dấu phẩy tách
 * nhiều slug trong một giá trị. Kết quả gom theo facet: lọc AND giữa các facet,
 * OR trong cùng facet.
 */
final class TagFilter
{
    /** @return array<string, list<string>> facet → danh sách slug */
    public static function slugsByFacet(Request $request): array
    {
        $values = [];
        foreach (explode('&', (string) $request->server('QUERY_STRING', '')) as $pair) {
            if ($pair === '') {
                continue;
            }
            [$key, $value] = array_pad(explode('=', $pair, 2), 2, '');
            if (urldecode($key) === 'tag' || urldecode($key) === 'tag[]') {
                $values[] = urldecode($value);
            }
        }
        if ($values === []) {
            $fallback = $request->query('tag');
            $values = is_array($fallback) ? $fallback : array_filter([$fallback]);
        }

        $byFacet = [];
        foreach ($values as $value) {
            foreach (explode(',', (string) $value) as $slug) {
                $slug = trim($slug);
                if ($slug !== '') {
                    $byFacet[str_contains($slug, ':') ? explode(':', $slug)[0] : ''][] = $slug;
                }
            }
        }

        return $byFacet;
    }
}
