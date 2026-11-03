<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Dish extends Model
{
    protected $guarded = [];

    /** Căn cứ nâng bù Mức 2 bằng cổng thông tin Mức 3 (TCM-000, kế hoạch Mức 3 §1). */
    public const LEVEL_BASIS_TCM_COMPENSATION = 'tcm_compensation';

    protected function casts(): array
    {
        return [
            'recipe_steps' => 'array',
            'estimated_kcal' => 'array',
            'external_ref' => 'array',
            'cultural_note' => 'array',
            'is_eat_out' => 'boolean',
            'is_home_cook' => 'boolean',
            'needs_review' => 'boolean',
            'reviewed_at' => 'datetime',
            'serving_grams' => 'float',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_dish_id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function names(): HasMany
    {
        return $this->hasMany(DishName::class);
    }

    /** Nhãn tiếng Việt có dấu cho mã cách chế biến lưu dạng slug trong CSDL. */
    public const COOKING_METHOD_LABELS = [
        'nau' => 'Nấu',
        'luoc' => 'Luộc',
        'hap' => 'Hấp',
        'kho' => 'Kho',
        'xao' => 'Xào',
        'chien' => 'Chiên',
        'chien-ngap-dau' => 'Chiên ngập dầu',
        'ran' => 'Rán',
        'nuong' => 'Nướng',
        'tron' => 'Trộn',
        'song' => 'Sống',
    ];

    public function cookingMethodLabel(): ?string
    {
        if ($this->cooking_method === null) {
            return null;
        }

        return self::COOKING_METHOD_LABELS[$this->cooking_method] ?? $this->cooking_method;
    }

    /**
     * Tên hiển thị cho người dùng Việt: luôn ưu tiên tên chính tiếng Việt có
     * dấu, không phụ thuộc thứ tự dòng tên trong dữ liệu (DATA-009).
     */
    public function displayName(): ?string
    {
        $names = $this->relationLoaded('names') ? $this->names : $this->names()->get();
        $vi = $names->first(fn ($n) => $n->locale === 'vi' && $n->is_primary)
            ?? $names->first(fn ($n) => $n->locale === 'vi');

        return ($vi ?? $names->first())?->name;
    }

    public function mealSlots(): HasMany
    {
        return $this->hasMany(DishMealSlot::class);
    }

    public function dishIngredients(): HasMany
    {
        return $this->hasMany(DishIngredient::class);
    }

    public function nutrition(): HasOne
    {
        return $this->hasOne(DishNutrition::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(DishMedia::class);
    }

    /**
     * IMG-001: ảnh chính để hiển thị — URL phục vụ từ data/media/ qua route
     * /media, kèm license + ghi công; is_ai=true khi là ảnh AI minh hoạ
     * (license 'ai-minh-hoa') để giao diện gắn nhãn trung thực.
     *
     * @return array{url: string, license: string, attribution: ?string, is_ai: bool}|null
     */
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

    public function recipeSources(): HasMany
    {
        return $this->hasMany(DishRecipeSource::class);
    }

    /** Tổng hợp tính vị do máy tính (TCM-002) — null khi chưa có nhãn nào đủ để tổng hợp. */
    public function tcmSummary(): HasOne
    {
        return $this->hasOne(DishTcmSummary::class);
    }

    /**
     * Nhãn mức hoàn thiện để hiển thị: món nâng bù theo đường Mức 3 phải
     * phân biệt rõ với Mức 2 đầy đủ — "Mức 2 (có note)" (TCM-000).
     */
    public function levelLabel(): string
    {
        $label = 'Mức '.$this->completeness_level;
        if ($this->level_basis === self::LEVEL_BASIS_TCM_COMPENSATION) {
            $label .= ' (có note)';
        }

        return $label;
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'dish_tags');
    }
}
