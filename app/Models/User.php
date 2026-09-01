<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'height',
        'weight',
        'birthday',
        'age',
        'gender',
        'diseases',
        'wbc',
        'rbc',
        'hgb',
        'hct',
        'mcv',
        'mch',
        'mchc',
        'plt',
        'rdw_sd',
        'rdw_cv',
        'pdw',
        'mpv',
        'p_lcr',
        'pct',
        'neu',
        'lym',
        'mono',
        'eos',
        'baso',
        'tsh',
        'ast',
        'alt',
        'bilirubin',
        'alp',
        'ggtp',
        'total_cholesterol',
        'hdl_cholesterol',
        'non_hdl_cholesterol',
        'ldl_cholesterol',
        'triglycerides',
        'blood_recommendations',
        'proper_weight',
        'proper_pressure',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
        'google_id',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'age' => 'integer',
            'height' => 'decimal:2',
            'weight' => 'decimal:2',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birthday' => 'date:Y-m-d',
            'wbc' => 'decimal:3',
            'rbc' => 'decimal:3',
            'hgb' => 'decimal:3',
            'hct' => 'decimal:3',
            'mcv' => 'decimal:3',
            'mch' => 'decimal:3',
            'mchc' => 'decimal:3',
            'plt' => 'decimal:3',
            'rdw_sd' => 'decimal:3',
            'rdw_cv' => 'decimal:3',
            'pdw' => 'decimal:3',
            'mpv' => 'decimal:3',
            'p_lcr' => 'decimal:3',
            'pct' => 'decimal:3',
            'neu' => 'decimal:3',
            'lym' => 'decimal:3',
            'mono' => 'decimal:3',
            'eos' => 'decimal:3',
            'baso' => 'decimal:3',
            'tsh' => 'decimal:3',
            'ast' => 'decimal:3',
            'alt' => 'decimal:3',
            'bilirubin' => 'decimal:3',
            'alp' => 'decimal:3',
            'ggtp' => 'decimal:3',
            'total_cholesterol' => 'decimal:3',
            'hdl_cholesterol' => 'decimal:3',
            'non_hdl_cholesterol' => 'decimal:3',
            'ldl_cholesterol' => 'decimal:3',
            'triglycerides' => 'decimal:3',
        ];
    }

    public function weights(): HasMany
    {
        return $this->hasMany(Weight::class);
    }

    public function bloodPressures(): HasMany
    {
        return $this->hasMany(BloodPressure::class);
    }

    public function blood_pressures(): HasMany
    {
        return $this->bloodPressures();
    }

    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    public function diets(): HasMany
    {
        return $this->hasMany(Diet::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }
}
