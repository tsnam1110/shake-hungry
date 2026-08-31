<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DishMedia extends Model
{
    public $timestamps = false;

    protected $table = 'dish_media';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }
}
