<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ingredient extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'nutrition_snapshot' => 'array',
            'alternatives' => 'array',
            'secondary_sources' => 'array',
            'is_ready_to_eat' => 'boolean',
            'needs_review' => 'boolean',
            'reviewed_at' => 'datetime',
            'density_g_per_ml' => 'float',
            'default_yield_factor' => 'float',
            'oil_absorption_pct' => 'float',
            'edible_portion_pct' => 'float',
        ];
    }

    public function referenceFood(): BelongsTo
    {
        return $this->belongsTo(ReferenceFood::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(IngredientUnit::class);
    }

    /** Tên gọi khác / tên vùng miền (ALIAS-001). */
    public function aliases(): HasMany
    {
        return $this->hasMany(IngredientAlias::class);
    }

    /** Ảnh nguyên liệu (IMG-001) — hoa quả dùng chung media của nguyên liệu này. */
    public function media(): HasMany
    {
        return $this->hasMany(IngredientMedia::class);
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

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'ingredient_tags');
    }

    /** Nhãn tính vị theo từng nguồn (QT-6) — nhiều dòng khi nguồn mâu thuẫn. */
    public function tcmTraits(): HasMany
    {
        return $this->hasMany(IngredientTcmTrait::class);
    }

    /**
     * Thành phần cấu thành khi đây là nguyên liệu hỗn hợp (TCM-000) —
     * liên kết để người dùng tự vào xem từng thành phần.
     */
    public function components(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'ingredient_components',
            'ingredient_id',
            'component_ingredient_id',
        );
    }

    /** Các nguyên liệu hỗn hợp có dùng nguyên liệu này làm thành phần. */
    public function componentOf(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'ingredient_components',
            'component_ingredient_id',
            'ingredient_id',
        );
    }
}
