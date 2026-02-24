<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

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
        ];
    }

    /**
     * Get the reservations associated with the user.
     *
     * This defines a one-to-many relationship between the User model
     * and the Reservation model, where a user can have multiple reservations.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Get all ban records associated with the user.
     *
     * This defines a one-to-many relationship between the User model
     * and the Ban model, allowing the system to track multiple bans.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bans()
    {
        return $this->hasMany(Ban::class);
    }

    /**
     * Check if the user is currently banned.
     *
     * This method checks if the user has any active bans.
     * A user is considered banned if there is at least one record in
     * the `bans` relationship table, regardless of whether the ban is expired or not.
     *
     * @return bool
     */
    public function isBanned(): bool
    {
        return $this->bans()->exists();
    }

    /**
     * Get all timeout records associated with the user.
     *
     * This defines a one-to-many relationship between the User model
     * and the Timeout model, since a user can be timed out multiple times.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function timeouts()
    {
        return $this->hasMany(Timeout::class);
    }

    /**
     * Check if the user is currently timed out.
     *
     * This method checks if the user has any active timeouts.
     * A timeout is considered active if the `expires_at` timestamp is in the future.
     * The method will return `true` if any of the user's timeouts are still active,
     * and `false` otherwise.
     *
     * @return bool
     */
    public function isTimedOut(): bool
    {
        return $this->timeouts()->where('expires_at', '>', now())->exists();
    }
}
