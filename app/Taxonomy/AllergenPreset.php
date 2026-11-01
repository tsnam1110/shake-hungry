<?php

declare(strict_types=1);

namespace App\Taxonomy;

use App\Models\Ingredient;

/**
 * Danh sách slug loại trừ dị ứng cho giao diện Lắc + Hồ sơ (AUDIT-ALLERGEN FIX-A1).
 *
 * SINH TỪ TaggingRules trên toàn bộ catalog nguyên liệu — nguồn sự thật duy nhất.
 * Thay preset slug viết tay đóng băng từ REC-001: preset cũ thiếu hẳn chip
 * Cá / Động vật có vỏ / Mè và thiếu hàng loạt slug có nhãn (nuoc-tuong,
 * tom-cang-xanh, ghe, mam-ruoc...) nên lọc cứng rò rỉ (đo bằng
 * Recommender::pool(): đậu nành 36 món, tôm 22, lúa mì 11...). Từ nay nhãn
 * thay đổi ở TaggingRules thì danh sách loại trừ tự đổi theo; test
 * AllergenExclusionTest khoá tính chất này.
 *
 * Thứ tự nhóm + tên hiển thị lấy từ config/taxonomy.php (facet allergen) —
 * cùng tên với badge "Dị ứng: ..." ở trang chi tiết và lọc Thư viện.
 */
final class AllergenPreset
{
    /**
     * @return list<array{group: string, label: string, slugs: list<string>}>
     */
    public static function groups(): array
    {
        $byGroup = [];
        foreach (Ingredient::query()->pluck('category', 'slug') as $slug => $category) {
            foreach (TaggingRules::allergensFor((string) $slug, (string) $category) as $group) {
                $byGroup[$group][] = (string) $slug;
            }
        }

        $groups = [];
        foreach (config('taxonomy.tags') as $fullSlug => $meta) {
            if (($meta['facet'] ?? null) !== 'allergen') {
                continue;
            }
            $group = substr((string) $fullSlug, strlen('allergen:'));
            $slugs = $byGroup[$group] ?? [];
            sort($slugs);
            $groups[] = ['group' => $group, 'label' => $meta['name'], 'slugs' => $slugs];
        }

        return $groups;
    }

    /**
     * Dạng [[nhãn, [slug...]], ...] — đúng khuôn mảng JS ALLERGENS /
     * ALLERGEN_PRESETS mà shake.blade.php và profile.blade.php đang dùng.
     *
     * @return list<array{0: string, 1: list<string>}>
     */
    public static function chips(): array
    {
        return array_map(fn (array $g) => [$g['label'], $g['slugs']], self::groups());
    }
}
