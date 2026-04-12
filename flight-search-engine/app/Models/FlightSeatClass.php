<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlightSeatClass extends Model
{
    protected $fillable = [
        'flight_schedule_id',
        'seat_class',
        'seat_capacity',
        'available_seats',
        'class_price',
    ];

    protected function casts(): array
    {
        return [
            'seat_capacity' => 'integer',
            'available_seats' => 'integer',
            'class_price' => 'decimal:2',
        ];
    }

    public function flightSchedule(): BelongsTo
    {
        return $this->belongsTo(FlightSchedule::class);
    }
}
