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
