<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $guarded = [];

    // Mặc định trong bộ nhớ khớp mặc định DB, để hồ sơ mới tạo qua
    // updateOrCreate (không truyền cờ) vẫn trả false thay vì null.
    protected $attributes = [
        'allow_alcohol' => false,
    ];

    protected function casts(): array
    {
        return [
            'excluded_ingredients' => 'array',
            'height_cm' => 'float',
            'weight_kg' => 'float',
            'tray_dish_count' => 'integer',
            'tray_binh_dan' => 'boolean',
            'show_tcm' => 'boolean',
            'allow_alcohol' => 'boolean',
            'target_protein_g_per_day' => 'integer',
            'target_kcal_per_day' => 'integer',
        ];
    }
}
