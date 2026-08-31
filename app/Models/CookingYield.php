<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CookingYield extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'yield_pct' => 'float',
            'yield_min_pct' => 'float',
            'yield_max_pct' => 'float',
            'moisture_change_pct' => 'float',
            'fat_change_pct' => 'float',
            'imported_at' => 'datetime',
        ];
    }
}
