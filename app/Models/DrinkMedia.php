<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Ảnh của nước uống (Phần 3A) — mirror DishMedia, cùng ràng buộc R4 về license. */
class DrinkMedia extends Model
{
    public $timestamps = false;

    protected $table = 'drink_media';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }
}
