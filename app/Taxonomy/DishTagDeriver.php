<?php

declare(strict_types=1);

namespace App\Taxonomy;

use App\Models\Dish;

/**
 * Suy nhãn MÓN từ thành phần (TAXONOMY.md §6) — hàm thuần theo dữ liệu nguyên
 * liệu, dùng giống hệt nhau ở: DishSeeder (nạp dish_tags), validate-data V16
 * (đối chiếu trôi lệch) và test. Nhãn biên tập `loai:` KHÔNG suy ở đây — nó nằm
 * trong file món.
 *
 * Đầu vào mỗi dòng nguyên liệu: slug, category, tags (nhãn nguyên liệu),
 * quantity (gram). Chấp nhận cả dữ liệu từ file JSON (tags theo TaggingRules)
 * lẫn từ DB (tags đã nạp) vì cùng qua một hàm.
 */
final class DishTagDeriver
{
    /** Nhóm đạm theo slug nguyên liệu (null = không tính vào đạm chính). */
    public static function proteinGroupOf(string $slug, string $category): ?string
    {
        // Trứng tách trước: trung-ga/trung-vit nằm trong category 'thit'.
        if (in_array($slug, ['trung-ga', 'trung-vit', 'trung-cut', 'trung-vit-lon', 'trung-vit-muoi', 'trung-ga-non'], true)) {
            return 'trung';
        }
        // Không có nhóm trong từ vựng — để null thay vì gán sai (TAXONOMY.md §6):
        // ếch, thịt dê, con rươi.
        if (in_array($slug, ['ech', 'thit-de', 'ruoi'], true)) {
            return null;
        }
        if ($category === 'thit') {
            return match (true) {
                str_contains($slug, '-bo') || str_starts_with($slug, 'thit-bo') || $slug === 'gan-bo' || $slug === 'bap-bo' => 'thit-bo',
                // 'ga' phải đứng ở biên từ — tránh 'gan-lon' (gan lợn) thành gia cầm
                (bool) preg_match('/(^|-)ga(-|$)/', $slug) || str_contains($slug, 'vit') || str_contains($slug, 'chim') => 'gia-cam',
                default => 'thit-lon', // đa số nhóm thịt còn lại là lợn và chế phẩm từ lợn
            };
        }
        if ($category === 'thuy-san') {
            return match (true) {
                str_starts_with($slug, 'tom') || str_starts_with($slug, 'tep') || str_starts_with($slug, 'cua') || $slug === 'be-be' || $slug === 'ghe' => 'tom-cua',
                str_starts_with($slug, 'muc') || $slug === 'bach-tuoc' => 'muc',
                in_array($slug, ['oc-buou', 'oc-len', 'ngheu', 'hen', 'don', 'so-diep', 'hau', 'sua-bien', 'trai'], true) => 'oc',
                default => 'ca', // cá các loại + lươn + chả cá
            };
        }
        if (in_array($slug, ['dau-phu', 'dau-phu-chien', 'tau-hu-ky', 'mi-can'], true)
            || str_starts_with($slug, 'dau-') && str_ends_with($slug, '-hat')
            || in_array($slug, ['dau-ngu', 'dau-trang', 'lac-rang', 'lac-song'], true)) {
            return 'dau-phu-dau';
        }

        return null;
    }

    /**
     * @param  list<array{slug: string, category: string, tags: list<string>, quantity: float}>  $rows
     * @return list<string> bộ nhãn suy được: 1 diet + 1 protein + 0..n allergen
     */
    public static function derive(array $rows): array
    {
        $hasMeatLevel = false;   // thịt / hải sản / mắm / composite gốc động vật mức "mặn"
        $hasEggDairy = false;    // trứng / sữa / mật ong / composite mức trứng-sữa
        $allergens = [];
        $proteinMass = [];

        foreach ($rows as $row) {
            $tags = $row['tags'];
            $origin = self::tagValue($tags, 'origin');

            foreach ($tags as $t) {
                if (str_starts_with($t, 'allergen:')) {
                    $allergens[substr($t, 9)] = true;
                }
            }

            if ($origin === 'dong-vat') {
                // Trứng/sữa/mật ong là sản phẩm động vật nhưng không phải thịt:
                // chỉ hạ món xuống chay-trung-sua, không phải mặn.
                if (in_array('allergen:trung', $tags, true) || in_array('allergen:sua', $tags, true) || $row['slug'] === 'mat-ong') {
                    $hasEggDairy = true;
                } else {
                    $hasMeatLevel = true;
                }
            } elseif ($origin === 'tong-hop' && isset(TaggingRules::COMPOSITE_ANIMAL[$row['slug']])) {
                if (TaggingRules::COMPOSITE_ANIMAL[$row['slug']] === 'man') {
                    $hasMeatLevel = true;
                } else {
                    $hasEggDairy = true;
                }
            }

            $group = self::proteinGroupOf($row['slug'], $row['category']);
            if ($group !== null) {
                $proteinMass[$group] = ($proteinMass[$group] ?? 0.0) + $row['quantity'];
            }
        }

        $diet = $hasMeatLevel ? 'man' : ($hasEggDairy ? 'chay-trung-sua' : 'chay');

        $protein = 'khong';
        if ($proteinMass !== []) {
            arsort($proteinMass);
            $total = array_sum($proteinMass);
            $top = array_key_first($proteinMass);
            if ($proteinMass[$top] >= 20.0) {
                $protein = $top;
                // Hai nhóm đạm cùng ≥25% tổng và đều là đạm động vật ⇒ hỗn hợp.
                $animalGroups = ['thit-bo', 'thit-lon', 'gia-cam', 'ca', 'tom-cua', 'muc', 'oc', 'trung'];
                $second = array_slice($proteinMass, 1, 1, true);
                if ($second !== []) {
                    $secondKey = array_key_first($second);
                    if (in_array($top, $animalGroups, true) && in_array($secondKey, $animalGroups, true)
                        && $second[$secondKey] / $total >= 0.25 && $proteinMass[$top] / $total < 0.55) {
                        $protein = 'hon-hop';
                    }
                }
            }
        }

        return [
            'diet:'.$diet,
            'protein:'.$protein,
            ...array_map(fn ($a) => 'allergen:'.$a, array_keys($allergens)),
        ];
    }

    /** Suy nhãn cho một Dish model đã nạp quan hệ nguyên liệu + tags. */
    public static function deriveForDish(Dish $dish): array
    {
        $rows = $dish->dishIngredients->map(fn ($di) => [
            'slug' => $di->ingredient->slug,
            'category' => $di->ingredient->category,
            'tags' => $di->ingredient->tags->pluck('slug')->all(),
            'quantity' => (float) $di->quantity,
        ])->all();

        return self::derive($rows);
    }

    /** @param list<string> $tags */
    private static function tagValue(array $tags, string $facet): ?string
    {
        foreach ($tags as $t) {
            if (str_starts_with($t, $facet.':')) {
                return substr($t, strlen($facet) + 1);
            }
        }

        return null;
    }
}
