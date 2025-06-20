<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens;

    const SUPERADMIN_ROLE = 1;
    const ADMIN_ROLE = 2;
    const GROUP_LEADER_ROLE = 3;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'is_active'
    ];

    public function isSuperadmin(): bool
    {
        return $this->role === self::SUPERADMIN_ROLE;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ADMIN_ROLE;
    }

    public function isGroupLeader(): bool
    {
        return $this->role === self::GROUP_LEADER_ROLE;
    }

    public function isLastSuperadmin(): bool
    {
        return $this->isSuperadmin() && self::where('role', self::SUPERADMIN_ROLE)
            ->isActive()
            ->where('id', '!=', $this->id)
            ->count() == 0;
    }

    public function scopeIsActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => 'integer',
            'is_active' => 'boolean'
        ];
    }

    public function settings(): HasOne
    {
        return $this->hasOne(UserSetting::class);
    }

    public function clearPassword()
    {
        $this->attributes['password'] = '';
    }

    protected static function booted()
    {
        static::created(function (User $user) {
            $user->settings()->create();
        });
    }
}
