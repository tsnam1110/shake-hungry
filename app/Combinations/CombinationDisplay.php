<?php

declare(strict_types=1);

namespace App\Combinations;

use App\Models\Combination;
use App\Models\Dish;
use App\Models\Fruit;
use App\Models\Ingredient;

/**
 * FOOD-COMBINE-001 — tầng TRÌNH BÀY của các cặp kết hợp cần lưu ý.
 *
 * Chỉ đọc và định dạng để hiển thị (trang nguyên liệu/quả/món). Không tham
 * gia gợi ý, lọc hay chấm điểm ở bất kỳ đâu (nguyên tắc đỏ §7.5 của hồ sơ
 * nghiên cứu). Tuyến thuốc × thực phẩm chỉ hiện ở trang nguyên liệu/quả
 * liên quan, không gắn vào món hay Lắc.
 */
final class CombinationDisplay
{
    /** Dòng miễn trừ cố định — bắt buộc ở mọi nơi hiển thị (§7.5 mục 2). */
    public const DISCLAIMER = 'Thông tin tham khảo về an toàn thực phẩm và văn hoá ẩm thực; không thay thế tư vấn y tế. Nếu đang dùng thuốc, hãy hỏi bác sĩ hoặc dược sĩ của bạn.';

    /**
     * Toàn bộ cặp trong kho, để trang riêng "Kết hợp cần lưu ý" liệt kê.
     * Vẫn là lớp THÔNG TIN thuần: không lọc, không chặn, không chấm điểm.
     * Cặp thuốc × thực phẩm cũng nằm trong danh sách này (nhãn rõ là thuốc),
     * vì trang riêng là bảng tra cứu, không phải gợi ý món.
     */
    public static function all(): array
    {
        return Combination::query()->orderBy('slug')->get()
            ->map(fn (Combination $c) => self::format($c, null))
            ->values()->all();
    }

    /** Các cặp chạm tới một slug nguyên liệu/quả (hiển thị hai chiều). */
    public static function forItem(string $slug): array
    {
        return Combination::query()->orderBy('slug')->get()
            ->filter(fn (Combination $c) => Combination::sideMatchesSlug($c->side('a'), $slug)
                || Combination::sideMatchesSlug($c->side('b'), $slug))
            ->map(fn (Combination $c) => self::format($c, $slug))
            ->values()->all();
    }

    /**
     * Các cặp THỰC PHẨM–THỰC PHẨM mà món chứa cả hai phía.
     * Trả về hai nhóm: 'notes' (tầng 1–2, hiện ở khối nguyên liệu) và
     * 'folk' (tầng 3, hiện thu gọn). Cặp có phía thuốc không bao giờ vào món.
     */
    public static function forDish(Dish $dish): array
    {
        $slugs = $dish->dishIngredients->map(fn ($di) => $di->ingredient->slug)->all();
        $notes = [];
        $folk = [];
        foreach (Combination::query()->orderBy('slug')->get() as $c) {
            $a = $c->side('a');
            $b = $c->side('b');
            if (! Combination::sideIsFood($a) || ! Combination::sideIsFood($b)) {
                continue;
            }
            $hitA = collect($slugs)->contains(fn ($s) => Combination::sideMatchesSlug($a, $s));
            $hitB = collect($slugs)->contains(fn ($s) => Combination::sideMatchesSlug($b, $s));
            if (! ($hitA && $hitB)) {
                continue;
            }
            $row = self::format($c, null);
            if ($c->tier() <= 2) {
                $notes[] = $row;
            } else {
                $folk[] = $row;
            }
        }

        return ['notes' => $notes, 'folk' => $folk];
    }

    private static function format(Combination $c, ?string $focusSlug): array
    {
        $a = $c->side('a');
        $b = $c->side('b');
        // Khi xem trang một phía, "phía còn lại" là phía không khớp slug đó.
        $other = null;
        if ($focusSlug !== null) {
            $otherSide = Combination::sideMatchesSlug($a, $focusSlug) ? $b : $a;
            $other = [
                'type' => $otherSide['type'],
                'ref' => $otherSide['ref'],
                'label' => self::sideLabel($otherSide),
            ];
        }

        return [
            'slug' => $c->slug,
            'tier' => $c->tier(),
            'tier_label' => $c->tierLabel(),
            'category' => $c->category,
            'science_status' => $c->science_status,
            'science_label' => $c->scienceLabel(),
            'condition_vi' => $c->condition_vi,
            'mechanism_vi' => $c->mechanism_vi,
            'traditional_source' => $c->traditional_source,
            'sources' => $c->sources,
            'side_a_label' => self::sideLabel($a),
            'side_b_label' => self::sideLabel($b),
            // Ảnh dùng lại từ kho nguyên liệu/quả đã có (IMG-001), không vẽ
            // ảnh mới cho trang này. Phía không có ảnh trả null để view vẽ
            // placeholder, thay vì bịa một ảnh đại diện.
            'side_a_image' => self::sideImage($a),
            'side_b_image' => self::sideImage($b),
            'other' => $other,
        ];
    }

    /**
     * Ảnh chính dùng lại của một phía, kèm tên thành viên đại diện khi phía
     * là nhóm (để view ghi rõ, không để người đọc tưởng ảnh đó là cả nhóm).
     *
     * Quy tắc an toàn, ghi rõ để lần sau không "tiện tay" phá:
     * - drug_group: luôn null — kho không có ảnh thuốc, không vẽ vỉ thuốc giả.
     * - food_group dạng viên bổ sung (nghe/tỏi dạng viên, KHÔNG phải dạng
     *   nấu ăn theo đúng condition_vi của cặp): luôn null, dù thành viên
     *   có ảnh nấu ăn — dùng ảnh đó sẽ sai dạng dùng.
     * - food_group còn lại: ảnh của thành viên đầu tiên có ảnh, ghi rõ tên
     *   thành viên đó ở 'representative'.
     * - food_group không có thành viên / không thành viên nào có ảnh: null.
     *
     * @return null|array{url: string, thumb_url: string, srcset: string, license: ?string, attribution: ?string, is_ai: bool, representative: ?string}
     */
    private static function sideImage(array $side): ?array
    {
        return match ($side['type']) {
            'ingredient' => self::ingredientImage($side['ref']),
            'fruit' => self::fruitImage($side['ref']),
            'food_group' => self::foodGroupImage($side),
            default => null,
        };
    }

    private static function ingredientImage(string $slug): ?array
    {
        $ingredient = Ingredient::where('slug', $slug)->with('media')->first();
        if ($ingredient === null) {
            return null;
        }
        $image = $ingredient->primaryImage();
        if ($image === null) {
            return null;
        }

        return [...$image, 'representative' => null];
    }

    private static function fruitImage(string $slug): ?array
    {
        $fruit = Fruit::where('slug', $slug)->with('ingredient.media')->first();
        $image = $fruit?->ingredient?->primaryImage();
        if ($image === null) {
            return null;
        }

        return [...$image, 'representative' => null];
    }

    private static function foodGroupImage(array $side): ?array
    {
        // Dạng viên bổ sung, không phải dạng nấu ăn — cấm dùng ảnh nấu ăn.
        if (in_array($side['ref'], ['nghe-dang-bo-sung', 'toi-dang-bo-sung'], true)) {
            return null;
        }
        foreach ($side['members'] ?? [] as $memberSlug) {
            $ingredient = Ingredient::where('slug', $memberSlug)->with('media')->first();
            $image = $ingredient?->primaryImage();
            if ($image !== null) {
                return [...$image, 'representative' => $ingredient->name_vi];
            }
        }

        return null;
    }

    /** Nhãn hiển thị của một phía: tra catalog với phía thực phẩm đơn lẻ. */
    private static function sideLabel(array $side): string
    {
        return match ($side['type']) {
            'ingredient' => Ingredient::where('slug', $side['ref'])->value('name_vi') ?? $side['ref'],
            'fruit' => Fruit::where('slug', $side['ref'])->with('ingredient')->first()?->ingredient?->name_vi ?? $side['ref'],
            default => $side['label'] ?? $side['ref'],
        };
    }
}
