<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'flight_id',
        'status',
        'booking_reference',
        'seat_number',
    ];

    /**
     * Get the user that owns the booking.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the flight that is booked.
     */
    public function flight(): BelongsTo
    {
        return $this->belongsTo(Flight::class);
    }

    /**
     * Generate a unique booking reference using airline name as prefix.
     *
     * @param int $flightId
     * @return string
     */
    public static function generateBookingReference(int $flightId): string
    {
        // Get the flight and its associated airline
        $flight = Flight::with('airline')->findOrFail($flightId);
        $airlineName = $flight->airline->name;
        
        // Get first two letters of airline name and convert to uppercase
        $prefix = strtoupper(substr($airlineName, 0, 2));
        
        $randomPart = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
        $timestamp = date('ymd');
        
        return $prefix . $timestamp . $randomPart;
    }
}
