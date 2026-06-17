<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DietDay extends Model
{
    protected $fillable = ['diet_id', 'day', 'protein', 'fat', 'carbohydrates', 'content'];

    protected function casts(): array
    {
        return [
            'protein' => 'decimal:2',
            'fat' => 'decimal:2',
            'carbohydrates' => 'decimal:2',
        ];
    }

    public function diet(): BelongsTo
    {
        return $this->belongsTo(Diet::class);
    }
}
