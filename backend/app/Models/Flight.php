<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Flight extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'airline_id',
        'origin',
        'destination',
        'duration',
        'flight_date',
        'state',
        'passenger_capacity',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'flight_date' => 'datetime',
        'state' => 'string',
        'passenger_capacity' => 'integer',
    ];

    /**
     * Get the airline that owns the flight.
     */
    public function airline(): BelongsTo
    {
        return $this->belongsTo(Airline::class);
    }

    /**
     * Get the origin airport.
     */
    public function originAirport(): BelongsTo
    {
        return $this->belongsTo(Airport::class, 'origin', 'id');
    }

    /**
     * Get the destination airport.
     */
    public function destinationAirport(): BelongsTo
    {
        return $this->belongsTo(Airport::class, 'destination', 'id');
    }

    /**
     * Get all bookings for this flight.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
    
    /**
     * Get the users who booked this flight.
     */
    public function passengers()
    {
        return $this->hasManyThrough(
            User::class,
            Booking::class,
            'flight_id', // Foreign key on bookings table
            'id',        // Foreign key on users table
            'id',        // Local key on flights table
            'user_id'    // Local key on bookings table
        );
    }
    
    /**
     * Check if the flight has available seats.
     */
    public function hasAvailableSeats(): bool
    {
        $bookedSeats = $this->bookings()->where('status', '!=', 'cancelled')->count();
        return $bookedSeats < $this->passenger_capacity;
    }
    
    /**
     * Get the number of available seats.
     */
    public function availableSeats(): int
    {
        $bookedSeats = $this->bookings()->where('status', '!=', 'cancelled')->count();
        return max(0, $this->passenger_capacity - $bookedSeats);
    }
}
