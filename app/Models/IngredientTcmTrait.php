<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Nhãn tính vị của một nguyên liệu theo MỘT nguồn (QT-6, GĐ6).
 * Một nguyên liệu có nhiều dòng khi các nguồn mâu thuẫn — mâu thuẫn được
 * giữ và hiển thị, không ép chọn. `source_ref` bắt buộc (R5).
 * `usage_restricted`/`usage_note`: cờ theo chính sách lưu trữ nguồn hạn chế
 * (kế hoạch Mức 3 §2) — dữ liệu vẫn lưu trong thư viện, ghi chú "không được
 * dùng" đi kèm ở mọi nơi dữ liệu xuất hiện.
 */
class IngredientTcmTrait extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'usage_restricted' => 'boolean',
        ];
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
