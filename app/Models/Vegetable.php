<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Hồ sơ rau theo mùa — lớp phủ mùa vụ lên nguyên liệu rau (VEGETABLE-MODULE).
 * Theo mẫu Fruit: dinh dưỡng/ảnh lấy qua quan hệ ingredient khi có; model
 * này chỉ giữ nhóm, nguồn cung, trạng thái, tháng có bán theo vùng, tháng
 * chính vụ, ghi chú chọn/bảo quản/nấu và lưu ý an toàn sơ chế.
 *
 * QUY TẮC KHÔNG BỊA: tháng của một vùng để null nghĩa là chưa có lịch chợ
 * riêng cho vùng đó (không gán tháng); kcal null nghĩa là repo chưa có số
 * (không mượn số loài/giống khác); không có giá (cần đợt thu thập riêng).
 * Lưu ý an toàn ở đây là ghi chú sơ chế/nấu chín, KHÔNG phải cặp kết hợp:
 * chỉ cặp đã thẩm định trong data/combinations/ mới được seed/hiển thị
 * dưới dạng "Kết hợp cần lưu ý".
 */
class Vegetable extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'months_bac' => 'array',
            'months_trung' => 'array',
            'months_nam' => 'array',
            'region_notes' => 'array',
            'main_season_months' => 'array',
            'best_months' => 'array',
            'aliases' => 'array',
            'uncertain' => 'boolean',
            'needs_review' => 'boolean',
            'reviewed_at' => 'date',
        ];
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    /** Rau có bán ở vùng $region (bac/trung/nam) trong tháng $month không. */
    public function isAvailableIn(string $region, int $month): bool
    {
        $months = match ($region) {
            'bac' => $this->months_bac,
            'trung' => $this->months_trung,
            'nam' => $this->months_nam,
            default => null,
        };

        return $months !== null && in_array($month, $months, true);
    }

    public function isMainSeasonIn(int $month): bool
    {
        return $this->main_season_months !== null && in_array($month, $this->main_season_months, true);
    }
}
