<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DishTcmSummary extends Model
{
    public $timestamps = false;

    protected $table = 'dish_tcm_summary';

    protected $guarded = [];
}
