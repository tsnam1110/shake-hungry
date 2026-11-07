<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MealLog extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'eaten_at' => 'datetime',
        ];
    }

    public function dish(): BelongsTo
    {
        return $this->belongsTo(Dish::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
