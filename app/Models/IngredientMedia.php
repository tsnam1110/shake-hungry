<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Ảnh của nguyên liệu (IMG-001) — mirror DishMedia, cùng ràng buộc R4 về license. */
class IngredientMedia extends Model
{
    public $timestamps = false;

    protected $table = 'ingredient_media';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }
}
