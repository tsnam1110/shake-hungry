<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class WeekPlan extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'target_protein_g_per_day' => 'integer',
            'target_kcal_per_day' => 'integer',
            'budget_vnd' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function days(): HasMany
    {
        return $this->hasMany(WeekPlanDay::class)->orderBy('date');
    }

    public function meals(): HasManyThrough
    {
        return $this->hasManyThrough(WeekPlanMeal::class, WeekPlanDay::class);
    }
}
