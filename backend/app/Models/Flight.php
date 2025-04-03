<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'airplane_plate',
        'duration',
        'flight_date',
        'state',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'flight_date' => 'datetime',
        'state' => 'string',
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
     * Get the airplane for this flight.
     */
    public function airplane(): BelongsTo
    {
        return $this->belongsTo(Airplane::class, 'airplane_plate', 'plate');
    }
}
