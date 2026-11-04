<?php

declare(strict_types=1);

namespace App\Week;

use App\Models\WeekPlan;

/**
 * Danh sách đi chợ theo gram (Giai đoạn 2, Phần 2.6).
 *
 * Cộng gộp DishIngredient của mọi bữa trong tuần, KHÔNG quy đổi ra gói
 * và KHÔNG ghi giá — chưa có bảng giá/gói đủ nguồn để làm thật, và
 * không được bịa gói/giá. Nhãn rõ ràng ở API và giao diện.
 * - Bữa ăn ngoài bị loại khỏi danh sách vì người dùng không nấu bữa đó.
 * - Dòng có grams_resolved thì cộng theo gram × khẩu phần; dòng không có
 *   grams_resolved thì giữ nguyên quantity + unit_name, không cộng chung
 *   với gram và không tự đổi đơn vị (quả, củ, mớ không đổi ra gram được).
 * - Nhóm theo category sẵn có của nguyên liệu (thịt, rau củ, gia vị…),
 *   CHƯA phải gian hàng cửa hàng — mapping nhóm → gian hàng là task
 *   riêng ở Giai đoạn 3, không đoán ở đây.
 * - Dòng is_optional được gắn cờ optional; dòng is_discarded vẫn mua
 *   (nguyên liệu bỏ đi khi nấu vẫn phải mua về mới nấu được).
 */
class ShoppingListBuilder
{
    public const CATEGORY_LABELS = [
        'thit' => 'Thịt', 'thuy-san' => 'Thuỷ sản', 'rau-cu' => 'Rau củ',
        'rau-gia-vi' => 'Rau gia vị', 'trai-cay' => 'Trái cây',
        'tinh-bot' => 'Tinh bột', 'gia-vi' => 'Gia vị', 'dau-mo' => 'Dầu mỡ',
        'khac' => 'Khác',
    ];

    /** @return array<string, mixed> */
    public function build(WeekPlan $plan): array
    {
        $items = [];
        $plan->loadMissing('days.meals.dish.dishIngredients.ingredient');
        foreach ($plan->days as $day) {
            foreach ($day->meals as $meal) {
                if ($meal->is_eat_out || $meal->dish === null) {
                    continue;
                }
                $servings = (float) $meal->servings;
                foreach ($meal->dish->dishIngredients as $di) {
                    $ingredient = $di->ingredient;
                    if ($ingredient === null) {
                        continue;
                    }
                    $key = $ingredient->id;
                    if (! isset($items[$key])) {
                        $items[$key] = [
                            'ingredient_id' => $ingredient->id,
                            'slug' => $ingredient->slug,
                            'name' => $ingredient->name_vi,
                            'category' => $ingredient->category,
                            'category_label' => self::CATEGORY_LABELS[$ingredient->category] ?? $ingredient->category,
                            'total_grams' => 0.0,
                            'has_grams' => false,
                            'unit_entries' => [],
                            'optional' => true,
                            'meals_count' => 0,
                        ];
                    }
                    $items[$key]['meals_count']++;
                    if (! $di->is_optional) {
                        $items[$key]['optional'] = false;
                    }
                    if ($di->grams_resolved !== null) {
                        $items[$key]['total_grams'] += (float) $di->grams_resolved * $servings;
                        $items[$key]['has_grams'] = true;
                    } else {
                        $unit = $di->unit_name;
                        $found = false;
                        foreach ($items[$key]['unit_entries'] as &$entry) {
                            if ($entry['unit_name'] === $unit) {
                                $entry['quantity'] += (float) $di->quantity * $servings;
                                $found = true;
                                break;
                            }
                        }
                        unset($entry);
                        if (! $found) {
                            $items[$key]['unit_entries'][] = [
                                'unit_name' => $unit,
                                'quantity' => round((float) $di->quantity * $servings, 2),
                            ];
                        }
                    }
                }
            }
        }
        $list = array_values(array_map(function (array $item) {
            $item['total_grams'] = $item['has_grams'] ? round($item['total_grams'], 1) : null;
            unset($item['has_grams']);

            return $item;
        }, $items));
        usort($list, fn ($a, $b) => [$a['category'], $a['name']] <=> [$b['category'], $b['name']]);

        return [
            'plan_id' => $plan->id,
            'start_date' => $plan->start_date->toDateString(),
            'end_date' => $plan->end_date->toDateString(),
            'note' => 'Danh sách theo gram, chưa quy đổi ra gói và chưa có giá — chưa có bảng giá/gói đủ nguồn. Bữa ăn ngoài không tính vào danh sách.',
            'groups' => $this->groupByCategory($list),
            // Giai đoạn 3, Task 2: nhóm theo gian hàng chung (xem AisleMapper
            // — không phải sơ đồ của một cửa hàng cụ thể).
            'aisle_groups' => AisleMapper::group($list),
            'aisle_note' => 'Gian hàng chung theo nhóm nguyên liệu, không phải sơ đồ của một cửa hàng cụ thể; thứ tự chỉ là gợi ý đi chợ.',
            'items' => $list,
        ];
    }

    /** @param array<int, array<string, mixed>> $items */
    private function groupByCategory(array $items): array
    {
        $groups = [];
        foreach ($items as $item) {
            $groups[$item['category']]['category'] = $item['category'];
            $groups[$item['category']]['label'] = $item['category_label'];
            $groups[$item['category']]['items'][] = $item;
        }

        return array_values($groups);
    }
}
