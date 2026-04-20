<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $fillable = [
        'flight_schedule_id',
        'booking_code',
        'customer_email',
        'total_passengers',
        'ancillary_services',
        'status',
        'payment_method',
        'payment_expires_at',
        'total_price',
        'paid_at',
        'cancelled_at',
    ];

    protected $casts = [
        'ancillary_services' => 'array',
        'total_price' => 'decimal:2',
        'paid_at' => 'datetime',
        'payment_expires_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Relasi: Booking terhubung ke FlightSchedule
     */
    public function flightSchedule(): BelongsTo
    {
        return $this->belongsTo(FlightSchedule::class);
    }

    /**
     * Relasi: Booking memiliki banyak Passengers
     */
    public function passengers(): HasMany
    {
        return $this->hasMany(Passenger::class);
    }

    /**
     * Relasi: Booking memiliki banyak Tickets
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
