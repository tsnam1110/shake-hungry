<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FruitLog extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['eaten_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fruit(): BelongsTo
    {
        return $this->belongsTo(Fruit::class);
    }
}
