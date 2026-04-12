<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Route extends Model
{
    protected $fillable = [
        'origin_id',
        'destination_id',
        'route_code',
        'distance_km',
    ];

    protected function casts(): array
    {
        return [
            'distance_km' => 'integer',
        ];
    }

    public function originAirport(): BelongsTo
    {
        return $this->belongsTo(Airport::class, 'origin_id');
    }

    public function destinationAirport(): BelongsTo
    {
        return $this->belongsTo(Airport::class, 'destination_id');
    }

    public function flightSchedules(): HasMany
    {
        return $this->hasMany(FlightSchedule::class);
    }
}
