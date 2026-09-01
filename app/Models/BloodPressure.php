<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BloodPressure extends Model
{
    use HasFactory;

    protected $fillable = [
        'systolic',
        'diastolic',
        'date',
        'review',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'systolic' => 'integer',
            'diastolic' => 'integer',
            'date' => 'date:Y-m-d',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
