<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
