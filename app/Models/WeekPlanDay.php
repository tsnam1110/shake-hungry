<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WeekPlanDay extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'protein_g' => 'float',
            'kcal_min' => 'float',
            'kcal_max' => 'float',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(WeekPlan::class, 'week_plan_id');
    }

    public function meals(): HasMany
    {
        return $this->hasMany(WeekPlanMeal::class);
    }
}
