<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeekPlanMeal extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'servings' => 'float',
            'is_eat_out' => 'boolean',
        ];
    }

    public function day(): BelongsTo
    {
        return $this->belongsTo(WeekPlanDay::class, 'week_plan_day_id');
    }

    public function dish(): BelongsTo
    {
        return $this->belongsTo(Dish::class);
    }

    public function replacedFromDish(): BelongsTo
    {
        return $this->belongsTo(Dish::class, 'replaced_from_dish_id');
    }
}
