<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Cultural\TcmAggregator;
use App\Models\DishTcmSummary;
use Illuminate\Console\Command;

/**
 * lacdoi:recompute-tcm — tính lại dish_tcm_summary toàn bộ món từ nhãn
 * tính vị của nguyên liệu (TCM-002). Kết quả là dữ liệu dẫn xuất, tính
 * lại được bất cứ lúc nào; chạy sau mỗi đợt gieo nhãn (TCM-001a…) hoặc
 * khi quy ước tổng hợp thay đổi. Idempotent.
 */
class LacdoiRecomputeTcm extends Command
{
    protected $signature = 'lacdoi:recompute-tcm';

    protected $description = 'Tính lại tổng hợp tính vị (dish_tcm_summary) từ nhãn nguyên liệu — engine thuần quy ước, không nhập tay';

    public function handle(TcmAggregator $aggregator): int
    {
        $result = $aggregator->recomputeAll();

        $full = DishTcmSummary::where('coverage_pct', '>=', 60)->count();
        $partial = DishTcmSummary::where('coverage_pct', '>=', 30)->where('coverage_pct', '<', 60)->count();
        $below = DishTcmSummary::where('coverage_pct', '<', 30)->count();

        $this->info(sprintf(
            'Đã tổng hợp %d món (%d đạt phủ ≥60%%, %d phủ 30–60%%, %d dưới ngưỡng hiển thị 30%%), xoá %d tổng hợp cũ không còn căn cứ.',
            $result['computed'], $full, $partial, $below, $result['cleared'],
        ));

        return self::SUCCESS;
    }
}
