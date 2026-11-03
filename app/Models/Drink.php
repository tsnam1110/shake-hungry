<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Thư viện nước uống — theo catalogue nước uống Việt Nam v2 (2026-11-03).
 *
 * Hai kind, hiển thị khác nhau và KHÔNG được lẫn:
 * - pha-che: có công thức (ly, dung tích, kỹ thuật, nguyên liệu ml/g, bước làm).
 * - dong-goi: KHÔNG có công thức — chỉ khối "Thông tin nhãn" (bao bì, thành
 *   phần điển hình theo loại, khoảng đường/cồn theo loại). Khoảng trong
 *   catalogue là khoảng theo loại, dao động theo thương hiệu — không phải
 *   bảng dinh dưỡng của một sản phẩm cụ thể.
 *
 * QUY TẮC DINH DƯỠNG (chặn kỹ thuật §9 catalogue): ethanol ~7 kcal/g không
 * nằm trong protein/fat/carb của USDA, và phần lớn nguyên liệu bar chưa có
 * trong kho — vì vậy module này KHÔNG hiển thị kcal tính từ engine cho tới
 * khi (a) logic ethanol được bổ sung và (b) nguyên liệu được seed đủ. Mọi
 * món có cồn và mọi món thiếu nguyên liệu đều hiện "chưa tính được kcal".
 * Không bao giờ hiện số thiếu như số đầy đủ. Caffeine chỉ mức định tính,
 * không ghi mg. Cocktail không được gợi ý tự động trong Lắc món (chưa có
 * lọc tuổi) — module này chỉ là thư viện tra cứu.
 */
class Drink extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'garnish' => 'array',
            'ingredients' => 'array',
            'recipe_steps' => 'array',
            'safety_flags' => 'array',
            'food_pairing' => 'array',
            'sugar_range_g' => 'array',
            'alcohol_pct_range' => 'array',
            'brand_examples' => 'array',
            'recipe_sources' => 'array',
            'alcohol' => 'boolean',
            'uncertain' => 'boolean',
            'needs_review' => 'boolean',
            'reviewed_at' => 'date',
        ];
    }

    public function isAlcoholic(): bool
    {
        return $this->alcohol;
    }

    public function isPackaged(): bool
    {
        return $this->kind === 'dong-goi';
    }
}
