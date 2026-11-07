<?php

declare(strict_types=1);

namespace App\Taxonomy;

use App\Models\Dish;
use App\Models\Drink;
use App\Models\Ingredient;

/**
 * QD-ALLERGEN (chủ dự án duyệt 2026-11-02, QD-01 = B và QD-02 = B) — tầng
 * HIỂN THỊ dị ứng.
 *
 * Lớp này KHÔNG tham gia lọc. Lọc cứng vẫn chạy trên cờ món do
 * DishTagDeriver suy ra + preset sinh từ TaggingRules (ALLERGEN-FIX) và giữ
 * nguyên 100% — một món hiển thị "Nước mắm" hay "có thể có" vẫn bị loại
 * khỏi pool() đúng như trước. Lớp này chỉ quyết định NHÃN TRÌNH BÀY:
 *
 * - QD-01: nhóm `ca` tách nhãn "Cá" (có cá thật hoặc chế phẩm cá như giò/
 *   chả cá — tức có ít nhất một đóng góp ngoài FISH_SAUCE_SLUGS) và
 *   "Nước mắm" (MỌI đóng góp vào nhóm cá đều từ dẫn xuất mắm cá lên men).
 * - QD-02: nhóm mà MỌI dòng đóng góp đều là nguyên liệu TUỲ CHỌN
 *   (is_optional) nhận certainty "possible" → giao diện hiển thị dạng phụ
 *   "Có thể có: …".
 *
 * Đóng góp được mở rộng qua liên kết thành phần (components) đúng như
 * DishTagDeriver::deriveForDish, nên tập nhóm trả về luôn BẰNG tập cờ dị
 * ứng của món — tính chất này có test census khoá (AllergenDisplayTest).
 */
final class AllergenDisplay
{
    /**
     * Dẫn xuất mắm cá lên men — đóng góp dạng "mắm cá" cho nhóm `ca`
     * (QD-01). Danh sách chốt theo census AUDIT-ALLERGEN §3: đây là toàn bộ
     * nguyên liệu mang allergen:ca mà bản chất không phải cá/chế phẩm cá.
     *
     * @var list<string>
     */
    public const FISH_SAUCE_SLUGS = [
        'nuoc-mam-cong-nghiep',
        'nuoc-mam-truyen-thong',
        'mam-ca-linh',
        'mam-ca-sac',
        'mam-nem',
    ];

    /**
     * Suy nhãn hiển thị từ các dòng nguyên liệu của MỘT món.
     *
     * @param  list<array{slug: string, tags: list<string>, components?: list<array<string, mixed>>, is_optional?: bool}>  $rows
     * @return list<array{group: string, label: string, certainty: string}>
     *                                                                      certainty: 'definite' | 'possible'
     */
    public static function fromRows(array $rows): array
    {
        // group => list of ['source' => slug, 'optional' => bool]
        $contrib = [];
        foreach ($rows as $row) {
            $sources = [];
            self::collectSources($row, $sources, []);
            foreach ($sources as $src) {
                foreach (self::allergensOf($src['tags'] ?? []) as $group) {
                    $contrib[$group][] = [
                        'source' => $src['slug'],
                        'optional' => (bool) ($row['is_optional'] ?? false),
                    ];
                }
            }
        }

        $labels = [];
        foreach (config('taxonomy.tags', []) as $slug => $meta) {
            if (($meta['facet'] ?? null) === 'allergen') {
                $labels[substr((string) $slug, strlen('allergen:'))] = (string) $meta['name'];
            }
        }

        $out = [];
        foreach ($labels as $group => $name) {
            if (! isset($contrib[$group])) {
                continue;
            }
            $list = $contrib[$group];
            $certainty = collect($list)->every(fn (array $c) => $c['optional'])
                ? 'possible'
                : 'definite';
            $label = $name;
            if ($group === 'ca') {
                $sauceOnly = collect($list)->every(
                    fn (array $c) => in_array($c['source'], self::FISH_SAUCE_SLUGS, true)
                );
                if ($sauceOnly) {
                    $label = 'Nước mắm';
                }
            }
            $out[] = ['group' => $group, 'label' => $label, 'certainty' => $certainty];
        }

        return $out;
    }

    /**
     * Nhãn hiển thị cho một món đã có trong DB. Tự nạp dòng nguyên liệu kèm
     * nhãn + thành phần (không đụng quan hệ đã serialize của trang chi tiết).
     *
     * @return list<array{group: string, label: string, certainty: string}>
     */
    public static function forDish(Dish $dish): array
    {
        $rows = [];
        $lines = $dish->dishIngredients()
            ->with(['ingredient.tags', 'ingredient.components.tags'])
            ->get();
        foreach ($lines as $line) {
            $ingredient = $line->ingredient;
            if ($ingredient === null) {
                continue;
            }
            $rows[] = self::toRow($ingredient, (bool) $line->is_optional);
        }

        return self::fromRows($rows);
    }

    /**
     * Nhãn dị ứng cho một đồ uống, suy từ các dòng nguyên liệu ĐÃ GẮN hồ sơ.
     *
     * Khác với món ăn (mọi dòng đều gắn nguyên liệu, census đầy đủ), đồ uống
     * chỉ gắn một phần: hiện tại 6/230 mục gắn đủ, 122 gắn một phần, 102
     * không gắn dòng nào (kể cả đóng gói không có dòng nguyên liệu). Vì vậy
     * kết quả LUÔN kèm độ phủ (linked/total/coverage) và giao diện không
     * được đọc "không suy được dị ứng nào" thành "không có dị ứng" khi độ
     * phủ chưa đủ: dòng chưa gắn có thể chứa chất gây dị ứng mà hệ thống
     * chưa thấy. Ví dụ sữa đậu nành: dòng chính "sữa đậu nành 250 ml" chưa
     * gắn slug, chỉ đường gắn — suy ra không có dị ứng nào nhưng độ phủ
     * chỉ 1/2, nên phải hiện "chưa đủ dữ kiện", không phải "không ghi nhận".
     *
     * @return array{display: list<array{group: string, label: string, certainty: string}>, linked: int, total: int, coverage: string}
     *                                                                                                                                 coverage: 'full' | 'partial' | 'none'
     */
    public static function forDrink(Drink $drink): array
    {
        $lines = $drink->ingredients ?? [];
        $rows = [];
        foreach ($lines as $line) {
            $slug = (string) ($line['ingredient_slug'] ?? '');
            if ($slug === '') {
                continue;
            }
            $ingredient = Ingredient::where('slug', $slug)->with(['tags', 'components.tags'])->first();
            if ($ingredient === null) {
                continue;
            }
            $rows[] = self::toRow($ingredient, false);
        }

        $total = count($lines);
        $linked = count($rows);

        return [
            'display' => self::fromRows($rows),
            'linked' => $linked,
            'total' => $total,
            'coverage' => $total === 0 || $linked === 0 ? 'none' : ($linked === $total ? 'full' : 'partial'),
        ];
    }

    /**
     * @return array{slug: string, tags: list<string>, components: list<array<string, mixed>>, is_optional: bool}
     */
    private static function toRow(Ingredient $ingredient, bool $isOptional): array
    {
        return [
            'slug' => (string) $ingredient->slug,
            'tags' => $ingredient->tags->pluck('slug')->all(),
            'components' => $ingredient->components
                ->map(fn (Ingredient $c) => self::toRow($c, false))
                ->all(),
            'is_optional' => $isOptional,
        ];
    }

    /**
     * Thu thập (slug, tags) của một dòng và mọi thành phần lồng nhau — cùng
     * quy ước chống vòng lặp với DishTagDeriver::deriveForDish.
     *
     * @param  array{slug: string, tags?: list<string>, components?: list<array<string, mixed>>}  $row
     * @param  list<array{slug: string, tags: list<string>}>  $sources
     * @param  array<string, true>  $visited
     */
    private static function collectSources(array $row, array &$sources, array $visited): void
    {
        $slug = (string) ($row['slug'] ?? '');
        if ($slug === '' || isset($visited[$slug])) {
            return;
        }
        $visited[$slug] = true;
        $sources[] = ['slug' => $slug, 'tags' => $row['tags'] ?? []];
        foreach ($row['components'] ?? [] as $component) {
            self::collectSources($component, $sources, $visited);
        }
    }

    /**
     * @param  list<string>  $tags
     * @return list<string> khoá nhóm dị ứng (phần sau tiền tố `allergen:`)
     */
    private static function allergensOf(array $tags): array
    {
        $out = [];
        foreach ($tags as $tag) {
            if (str_starts_with((string) $tag, 'allergen:')) {
                $out[] = substr((string) $tag, strlen('allergen:'));
            }
        }

        return $out;
    }
}
