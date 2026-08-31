<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferenceFood extends Model
{
    public $timestamps = false;

    // Inflector của Laravel coi "food" là không đếm được → đoán 'reference_food'.
    protected $table = 'reference_foods';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'micronutrients' => 'array',
            'raw_payload' => 'array',
            'imported_at' => 'datetime',
            'kcal_per_100g' => 'float',
            'protein_g' => 'float',
            'fat_g' => 'float',
            'carb_g' => 'float',
            'fiber_g' => 'float',
        ];
    }
}
