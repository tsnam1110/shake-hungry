<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tổng hợp tính vị của món — DO MÁY TÍNH (App\Cultural\TcmAggregator),
 * không nhập tay (TCM-002). Dữ liệu dẫn xuất: không xuất ra data/ (QT-3),
 * tính lại bằng `lacdoi:recompute-tcm` hoặc lúc seed.
 */
class DishTcmSummary extends Model
{
    public $timestamps = false;

    protected $table = 'dish_tcm_summary';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'nature_score' => 'float',
            'coverage_pct' => 'float',
            'has_conflicts' => 'boolean',
            'computed_at' => 'datetime',
        ];
    }

    public function dish(): BelongsTo
    {
        return $this->belongsTo(Dish::class);
    }
}
