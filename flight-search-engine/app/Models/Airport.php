<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Airport extends Model
{
    protected $fillable = [
        'airport_code',
        'airport_name',
        'city_name',
        'country_name',
    ];

    /**
     * Routes where this airport is the origin.
     */
    public function outgoingRoutes(): HasMany
    {
        return $this->hasMany(Route::class, 'origin_id');
    }

    /**
     * Routes where this airport is the destination.
     */
    public function incomingRoutes(): HasMany
    {
        return $this->hasMany(Route::class, 'destination_id');
    }
}
