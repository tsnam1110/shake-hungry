<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Tên gọi khác (tên vùng miền) của nguyên liệu — ALIAS-001, mô phỏng DishName. */
class IngredientAlias extends Model
{
    public $timestamps = false;

    protected $guarded = [];
}
