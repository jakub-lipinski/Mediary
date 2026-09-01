<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Diet extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'calories',
        'meals',
        'like',
        'dislike',
        'notes',
        'documents',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'calories' => 'integer',
            'meals' => 'integer',
            'documents' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function days(): HasMany
    {
        return $this->hasMany(DietDay::class);
    }
}
