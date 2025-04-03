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
     * Generate a unique booking reference.
     *
     * @return string
     */
    public static function generateBookingReference(): string
    {
        $prefix = 'BK';
        $randomPart = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
        $timestamp = date('ymd');
        
        return $prefix . $timestamp . $randomPart;
    }
}
