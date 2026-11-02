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
            'other' => $other,
        ];
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
