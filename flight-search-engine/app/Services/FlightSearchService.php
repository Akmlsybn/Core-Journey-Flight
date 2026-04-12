<?php
declare(strict_types=1);

// filepath: app/Services/FlightSearchService.php

namespace App\Services;

use App\Repositories\FlightRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class FlightSearchService
{
    public function __construct(
        private readonly FlightRepository $flightRepository
    ) {
    }

    public function search(array $filters): Collection
    {
        return $this->flightRepository->searchAvailableFlights(
            origin: strtoupper((string) $filters['origin']),
            destination: strtoupper((string) $filters['destination']),
            departureDate: Carbon::parse($filters['departure_date'])->toDateString(),
            passengerCount: (int) $filters['passenger_count'],
            seatClass: (string) $filters['seat_class']
        );
    }
}