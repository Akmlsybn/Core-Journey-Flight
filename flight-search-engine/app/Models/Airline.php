<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Airline extends Model
{
    protected $fillable = [
        'airline_code',
        'airline_name',
    ];

    public function flightSchedules(): HasMany
    {
        return $this->hasMany(FlightSchedule::class);
    }
}
