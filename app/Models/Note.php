<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Note extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'mood',
        'energy_level',
        'stress_level',
        'sleep_hours',
        'water_intake',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'energy_level' => 'integer',
            'stress_level' => 'integer',
            'sleep_hours' => 'decimal:2',
            'water_intake' => 'decimal:2',
            'date' => 'date:Y-m-d',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
