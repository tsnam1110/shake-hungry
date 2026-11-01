<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Một dòng liên kết thành phần của nguyên liệu hỗn hợp (TCM-000):
 * nguyên liệu `ingredient_id` cấu thành từ nguyên liệu `component_ingredient_id`
 * (cả hai đều là nguyên liệu trong kho). Nguồn sự thật là khoá `components`
 * trong file data/ingredients (QT-3); bảng này là bản dựng để truy vấn/hiển thị.
 */
class IngredientComponent extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class, 'component_ingredient_id');
    }
}
