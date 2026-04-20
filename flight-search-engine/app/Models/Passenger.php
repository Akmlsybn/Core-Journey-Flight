<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Passenger extends Model
{
    protected $fillable = [
        'booking_id',
        'name',
        'id_number',
        'seat_class',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi: Passenger milik Booking
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
