<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DishNutrition extends Model
{
    public $timestamps = false;

    protected $table = 'dish_nutrition';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'missing_ingredients' => 'array',
            'unverified_ingredients' => 'array',
            'computed_at' => 'datetime',
            'kcal_min' => 'float',
            'kcal_max' => 'float',
            'protein_g' => 'float',
            'fat_g' => 'float',
            'carb_g' => 'float',
            'fiber_g' => 'float',
        ];
    }
}
