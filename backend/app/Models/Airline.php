<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Airline extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'enterprise_id',
    ];

    /**
     * Get the enterprise that owns the airline.
     */
    public function enterprise(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enterprise_id');
    }
}
