<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
 * Không bao giờ hiện số thiếu như số đầy đủ. Caffeine: mức định tính, và
 * mg/ly khi có căn cứ (khoảng tầm soát vẫn ghi rõ tầng, không ghi điểm đo
 * giả); ô thứ tư trong lưới macro không lấp bằng "Không cồn" — có cồn thì
 * hiện độ cồn, không cồn thì hiện chỉ số động (thường là kcal/100 ml chất
 * lỏng), không có thì hiện "?". Cocktail không được gợi ý tự động trong
 * Lắc món (chưa có lọc tuổi) — module này chỉ là thư viện tra cứu.
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
            'key_indicators' => 'array',
            'brand_examples' => 'array',
            'recipe_sources' => 'array',
            'alcohol' => 'boolean',
            'uncertain' => 'boolean',
            'needs_review' => 'boolean',
            'reviewed_at' => 'date',
        ];
    }

    public function media(): HasMany
    {
        return $this->hasMany(DrinkMedia::class);
    }

    /** IMG-001: ảnh chính để hiển thị — cùng khuôn với Dish::primaryImage(). */
    public function primaryImage(): ?array
    {
        $media = $this->media->firstWhere('is_primary', true) ?? $this->media->first();
        if ($media === null) {
            return null;
        }

        return [
            'url' => '/'.$media->path,
            // Thumbnail cho thẻ danh sách (quy tắc 2026-11-03): url gốc
            // giữ nguyên cho trang chi tiết; thẻ danh sách dùng srcset.
            'thumb_url' => \App\Support\Thumbnail::url($media->path, 800),
            'srcset' => \App\Support\Thumbnail::srcset($media->path),
            'license' => $media->license,
            'attribution' => $media->attribution,
            'is_ai' => $media->license === 'ai-minh-hoa',
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
