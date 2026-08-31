<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
            'density_g_per_ml' => 'float',
            'default_yield_factor' => 'float',
            'oil_absorption_pct' => 'float',
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
}
