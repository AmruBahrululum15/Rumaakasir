<?php

namespace App\Models;

use App\Notifications\BusinessResetPasswordNotification;
use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use BelongsToBusiness, HasFactory, HasRoles, Notifiable;

    /**
     * Nama guard untuk Spatie Permission.
     */
    protected $guard_name = 'web';

    /**
     * Field yang boleh diisi.
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'nik',
        'alamat',
        'no_telepon',
        'foto',
        'is_active',
        'status',
        'requested_role',
        'must_change_credentials',
    ];

    /**
     * Field yang disembunyikan.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casting.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'must_change_credentials' => 'boolean',
        ];
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function ownedBusinesses(): HasMany
    {
        return $this->hasMany(Business::class, 'owner_id');
    }

    /** Keep Laravel's password broker while applying the business branding. */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new BusinessResetPasswordNotification($token));
    }
}
