<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'excluded_ingredients' => 'array',
            'height_cm' => 'float',
            'weight_kg' => 'float',
            'tray_dish_count' => 'integer',
            'tray_binh_dan' => 'boolean',
            'show_tcm' => 'boolean',
        ];
    }
}
