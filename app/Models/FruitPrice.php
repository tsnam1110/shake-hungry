<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Một dòng giá tham khảo mùa vụ: quả × miền × tháng (FRUIT-MODULE §3).
 * Luôn đi kèm nguồn + ngày thu thập — không có giá vô danh.
 */
class FruitPrice extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'month' => 'integer',
            'price_min' => 'integer',
            'price_max' => 'integer',
            'collected_at' => 'date',
        ];
    }

    public function fruit(): BelongsTo
    {
        return $this->belongsTo(Fruit::class);
    }
}
