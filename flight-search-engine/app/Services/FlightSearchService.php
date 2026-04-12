<?php

namespace App\Services;

use App\Repositories\Contracts\FlightRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class FlightSearchService
{
    public function __construct(
        private readonly FlightRepositoryInterface $flightRepository
    ) {}

    /**
     * @return Collection<int, \App\Models\Airport>
     */
    public function getAirportsForForm(): Collection
    {
        return $this->flightRepository->getAirportsOrderedByCode();
    }

    /**
     * @param  array{origin: string, destination: string, departure_date: string}  $criteria
     * @return Collection<int, \App\Models\FlightSchedule>
     */
    public function search(array $criteria): Collection
    {
        return $this->flightRepository->searchSchedules(
            $criteria['origin'],
            $criteria['destination'],
            $criteria['departure_date']
        );
    }

    /**
     * Harga termurah (MIN class_price pada flight_seat_classes) per departure_date untuk rute yang dipilih.
     *
     * @param  array{origin: string, destination: string, year: int, month: int}  $criteria
     * @return array{origin: string, destination: string, year: int, month: int, range_start: string|null, range_end: string|null, days: list<array{date: string, min_price: float}>}
     */
    public function getCalendarPricing(array $criteria): array
    {
        $year = (int) $criteria['year'];
        $month = (int) $criteria['month'];
        $origin = $criteria['origin'];
        $destination = $criteria['destination'];

        $monthStart = CarbonImmutable::create($year, $month, 1)->startOfDay();
        $monthEnd = $monthStart->endOfMonth();

        $today = CarbonImmutable::today();
        $rangeStart = $monthStart->greaterThan($today) ? $monthStart : $today;
        $rangeEnd = $monthEnd;

        if ($rangeStart->greaterThan($rangeEnd)) {
            return [
                'origin' => $origin,
                'destination' => $destination,
                'year' => $year,
                'month' => $month,
                'range_start' => null,
                'range_end' => null,
                'days' => [],
            ];
        }

        $rows = $this->flightRepository->getMinimumClassPriceByDepartureDate(
            $origin,
            $destination,
            $rangeStart->toDateString(),
            $rangeEnd->toDateString()
        );

        $days = $rows->map(function (object $row): array {
            $date = $row->departure_date;
            if ($date instanceof \DateTimeInterface) {
                $date = $date->format('Y-m-d');
            } else {
                $date = (string) $date;
            }

            return [
                'date' => $date,
                'min_price' => (float) $row->min_price,
            ];
        })->values()->all();

        return [
            'origin' => $origin,
            'destination' => $destination,
            'year' => $year,
            'month' => $month,
            'range_start' => $rangeStart->toDateString(),
            'range_end' => $rangeEnd->toDateString(),
            'days' => $days,
        ];
    }
}
