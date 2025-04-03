<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Airport extends Model
{
    use HasFactory;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The "type" of the primary key.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'name',
        'country',
    ];

    /**
     * Get the departing flights from this airport.
     */
    public function departingFlights(): HasMany
    {
        return $this->hasMany(Flight::class, 'origin', 'id');
    }

    /**
     * Get the arriving flights to this airport.
     */
    public function arrivingFlights(): HasMany
    {
        return $this->hasMany(Flight::class, 'destination', 'id');
    }
}
