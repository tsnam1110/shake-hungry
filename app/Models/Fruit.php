<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Hồ sơ hoa quả — lớp phủ lên nguyên liệu trai-cay (FRUIT-MODULE.md).
 * Dinh dưỡng/dị ứng lấy qua quan hệ ingredient; model này chỉ giữ phần
 * mùa vụ, thị trường, canh tác và kết hợp thực phẩm.
 */
class Fruit extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'grown_in' => 'array',
            'availability_months' => 'array',
            'main_season_months' => 'array',
            'best_nutrition_months' => 'array',
            'best_value_months' => 'array',
            'pairing_cautions' => 'array',
            'cultural_note' => 'array',
            'needs_review' => 'boolean',
            'reviewed_at' => 'date',
        ];
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(FruitPrice::class);
    }

    /** Quả có đang vào tháng có hàng ở tháng $month không. */
    public function isAvailableIn(int $month): bool
    {
        return in_array($month, $this->availability_months ?? [], true);
    }

    public function isMainSeasonIn(int $month): bool
    {
        return in_array($month, $this->main_season_months ?? [], true);
    }
}
