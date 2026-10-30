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
 *
 * Cờ --with-ingredients: tính thêm cả món Mức 1 ĐÃ CÓ danh sách nguyên liệu —
 * dùng cho đợt dữ liệu nháp chờ người duyệt (DISH-001 đợt 1, 2026-10-30), khi
 * món chưa đủ recipe_sources để lên Mức 2 (cổng V12) nhưng vẫn cần số dinh
 * dưỡng tham khảo. Số vẫn do engine tính và mang confidence của engine.
 */
class LacdoiRecomputeCalories extends Command
{
    protected $signature = 'lacdoi:recompute-calories {--with-ingredients : Tính cả món Mức 1 đã có nguyên liệu (dữ liệu nháp chờ duyệt)}';

    protected $description = 'Tính lại dinh dưỡng toàn bộ món từ bảng nguyên liệu (engine thuần tuý, QT-1)';

    public function handle(DishNutritionService $service): int
    {
        $computed = 0;
        $unknown = 0;

        $dishes = Dish::where('completeness_level', '>=', 2)
            ->when($this->option('with-ingredients'), fn ($q) => $q
                ->orWhere(fn ($q2) => $q2->where('completeness_level', 1)->has('dishIngredients')))
            ->get();
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

        $level1NoCompute = Dish::where('completeness_level', 1)
            ->when($this->option('with-ingredients'), fn ($q) => $q->doesntHave('dishIngredients'))
            ->count();
        $this->info("Đã tính {$computed} món, {$unknown} món chưa đủ dữ liệu, {$level1NoCompute} món Mức 1 (chỉ có estimated_kcal).");

        return self::SUCCESS;
    }
}
