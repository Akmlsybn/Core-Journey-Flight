<?php

namespace App\Repositories;

use App\Models\Airport;
use App\Models\FlightSchedule;
use App\Repositories\Contracts\FlightRepositoryInterface;
use Illuminate\Support\Collection;

class FlightRepository implements FlightRepositoryInterface
{
    public function getAirportsOrderedByCode(): Collection
    {
        return Airport::query()
            ->orderBy('airport_code')
            ->get();
    }

    public function searchSchedules(string $origin, string $destination, string $departureDate): Collection
    {
        return FlightSchedule::query()
            ->with([
                'airline',
                'route.originAirport',
                'route.destinationAirport',
                'seatClasses',
            ])
            ->where('origin', $origin)
            ->where('destination', $destination)
            ->whereDate('departure_date', $departureDate)
            ->orderBy('departure_time')
            ->get();
    }

    public function getMinimumClassPriceByDepartureDate(
        string $origin,
        string $destination,
        string $rangeStart,
        string $rangeEnd
    ): Collection {
        return FlightSchedule::query()
            ->join('flight_seat_classes', 'flight_seat_classes.flight_schedule_id', '=', 'flight_schedules.id')
            ->where('flight_schedules.origin', $origin)
            ->where('flight_schedules.destination', $destination)
            ->whereBetween('flight_schedules.departure_date', [$rangeStart, $rangeEnd])
            ->groupBy('flight_schedules.departure_date')
            ->orderBy('flight_schedules.departure_date')
            ->selectRaw('flight_schedules.departure_date as departure_date, MIN(flight_seat_classes.class_price) as min_price')
            ->get();
    }
}
