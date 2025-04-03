<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\User
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $phone
 * @property string $user_type User type: 'client' or 'enterprise'
 * @method bool isClient() Check if the user is a client
 * @method bool isEnterprise() Check if the user is an enterprise
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'user_type',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Check if the user is a client.
     *
     * @return bool True if the user is a client, false otherwise
     */
    public function isClient(): bool
    {
        return $this->user_type === 'client';
    }

    /**
     * Check if the user is an enterprise.
     *
     * @return bool True if the user is an enterprise, false otherwise
     */
    public function isEnterprise(): bool
    {
        return $this->user_type === 'enterprise';
    }

    /**
     * Get all bookings for the user.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get flights booked by this user through bookings.
     */
    public function flights()
    {
        return $this->hasManyThrough(
            Flight::class, 
            Booking::class,
            'user_id', // Foreign key on bookings table
            'id',      // Foreign key on flights table
            'id',      // Local key on users table
            'flight_id' // Local key on bookings table
        );
    }
}
