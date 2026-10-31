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

    protected function casts(): array
    {
        return [
            'recipe_steps' => 'array',
            'estimated_kcal' => 'array',
            'external_ref' => 'array',
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

    public function recipeSources(): HasMany
    {
        return $this->hasMany(DishRecipeSource::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'dish_tags');
    }
}
