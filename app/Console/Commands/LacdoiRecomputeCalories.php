<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Dish;
use App\Nutrition\DishNutritionService;
use Illuminate\Console\Command;

/**
 * lacdoi:recompute-calories — tính lại dinh dưỡng toàn bộ món sau khi sửa
 * bảng nguyên liệu hoặc nâng phiên bản engine (CLAUDE.md §1).
 *
 * Chỉ món completeness_level >= 2 có danh sách nguyên liệu mới tính được;
 * kết quả 'unknown' ⇒ xoá bản ghi dinh dưỡng cũ, món hiển thị "chưa đủ dữ liệu".
 */
class LacdoiRecomputeCalories extends Command
{
    protected $signature = 'lacdoi:recompute-calories';

    protected $description = 'Tính lại dinh dưỡng toàn bộ món từ bảng nguyên liệu (engine thuần tuý, QT-1)';

    public function handle(DishNutritionService $service): int
    {
        $computed = 0;
        $unknown = 0;

        $dishes = Dish::where('completeness_level', '>=', 2)->get();
        foreach ($dishes as $dish) {
            $row = $service->computeAndStore($dish, $service->pickFctSource($dish));
            if ($row === null) {
                $unknown++;
                $this->warn("  {$dish->slug}: chưa đủ dữ liệu (confidence=unknown) — không ghi số nào (QT-5)");
            } else {
                $computed++;
                $this->line(sprintf(
                    '  %s: ≈ %s – %s kcal · tin cậy: %s%s',
                    $dish->slug, $row->kcal_min, $row->kcal_max, $row->confidence,
                    $row->missing_ingredients === [] ? '' : ' · thiếu: '.implode(', ', $row->missing_ingredients),
                ));
            }
        }

        $this->info("Đã tính {$computed} món, {$unknown} món chưa đủ dữ liệu, ".Dish::where('completeness_level', 1)->count().' món Mức 1 (chỉ có estimated_kcal).');

        return self::SUCCESS;
    }
}
