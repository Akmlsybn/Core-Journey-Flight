<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FlightSchedule extends Model
{
    protected $fillable = [
        'airline_id',
        'route_id',
        'flight_number',
        'origin',
        'destination',
        'departure_date',
        'departure_time',
        'arrival_time',
        'base_price',
        'flight_status',
    ];

    protected function casts(): array
    {
        return [
            'departure_date' => 'date',
            'base_price' => 'decimal:2',
        ];
    }

    public function airline(): BelongsTo
    {
        return $this->belongsTo(Airline::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function seatClasses(): HasMany
    {
        return $this->hasMany(FlightSeatClass::class);
    }
}
