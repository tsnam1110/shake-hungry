<?php

declare(strict_types=1);

namespace App\Week;

/**
 * Mapping nhóm nguyên liệu → gian hàng đi chợ (Giai đoạn 3, Task 2).
 *
 * Đây là mapping TRÌNH BÀY chung, suy từ category sẵn có của nguyên liệu,
 * KHÔNG phải sơ đồ của một cửa hàng cụ thể nào (Bách Hóa Xanh, WinMart,
 * chợ truyền thống mỗi nơi một cách xếp khác nhau, và chưa có nguồn nào
 * cho sơ đồ đó). Vì vậy giao diện phải ghi rõ "gian hàng chung" và thứ tự
 * chỉ là thứ tự gợi ý đi chợ, không được trình bày như bố trí thật của
 * một cửa hàng. Khi có dữ liệu gian hàng theo cửa hàng thật, mapping này
 * được thay bằng bảng theo cửa hàng, không sửa ngược logic cộng gộp.
 */
final class AisleMapper
{
    /** category => [aisle, order] */
    public const MAP = [
        'rau-cu' => ['Quầy rau · trái cây', 1],
        'rau-gia-vi' => ['Quầy rau · trái cây', 1],
        'trai-cay' => ['Quầy rau · trái cây', 1],
        'thit' => ['Quầy thịt · thủy sản', 2],
        'thuy-san' => ['Quầy thịt · thủy sản', 2],
        'tinh-bot' => ['Quầy gạo · mì · bột', 3],
        'gia-vi' => ['Quầy gia vị · dầu mỡ', 4],
        'dau-mo' => ['Quầy gia vị · dầu mỡ', 4],
        'khac' => ['Quầy khác', 5],
    ];

    public static function aisleFor(string $category): string
    {
        return self::MAP[$category][0] ?? 'Quầy khác';
    }

    public static function orderFor(string $category): int
    {
        return self::MAP[$category][1] ?? 5;
    }

    /** @param array<int, array<string, mixed>> $items */
    public static function group(array $items): array
    {
        $groups = [];
        foreach ($items as $item) {
            $aisle = self::aisleFor($item['category']);
            if (! isset($groups[$aisle])) {
                $groups[$aisle] = ['aisle' => $aisle, 'order' => self::orderFor($item['category']), 'items' => []];
            }
            $groups[$aisle]['items'][] = $item;
        }
        usort($groups, fn ($a, $b) => $a['order'] <=> $b['order']);

        return array_values($groups);
    }
}
