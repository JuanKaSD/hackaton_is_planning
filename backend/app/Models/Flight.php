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
        'status',
        'passenger_capacity',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'flight_date' => 'datetime',
        'passenger_capacity' => 'integer',
    ];
    
    /**
     * The possible status values for a flight.
     */
    const STATUS_AVAILABLE = 'available';
    const STATUS_UNAVAILABLE = 'unavailable';
    const STATUS_CANCELED = 'canceled';
    
    /**
     * Check if the flight is available for booking.
     */
    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE;
    }
    
    /**
     * Check if the flight is unavailable.
     */
    public function isUnavailable(): bool
    {
        return $this->status === self::STATUS_UNAVAILABLE;
    }
    
    /**
     * Check if the flight is canceled.
     */
    public function isCanceled(): bool
    {
        return $this->status === self::STATUS_CANCELED;
    }
    
    /**
     * Mark the flight as available.
     */
    public function markAsAvailable(): self
    {
        $this->status = self::STATUS_AVAILABLE;
        $this->save();
        return $this;
    }
    
    /**
     * Mark the flight as unavailable.
     */
    public function markAsUnavailable(): self
    {
        $this->status = self::STATUS_UNAVAILABLE;
        $this->save();
        return $this;
    }
    
    /**
     * Mark the flight as canceled.
     */
    public function markAsCanceled(): self
    {
        $this->status = self::STATUS_CANCELED;
        $this->save();
        return $this;
    }

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
