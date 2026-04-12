<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface FlightRepositoryInterface
{
    /**
     * @return Collection<int, \App\Models\Airport>
     */
    public function getAirportsOrderedByCode(): Collection;

    /**
     * @return Collection<int, \App\Models\FlightSchedule>
     */
    public function searchSchedules(string $origin, string $destination, string $departureDate): Collection;

    /**
     * Harga kelas termurah per tanggal keberangkatan untuk rute (kode bandara).
     *
     * @return Collection<int, object{departure_date: string, min_price: string}>
     */
    public function getMinimumClassPriceByDepartureDate(
        string $origin,
        string $destination,
        string $rangeStart,
        string $rangeEnd
    ): Collection;

    /**
     * Cari penerbangan tersedia dengan filter ketersediaan kursi dan kelas.
     *
     * @return Collection<int, \App\Models\FlightSchedule>
     */
    public function searchAvailableFlights(
        string $origin,
        string $destination,
        string $departureDate,
        int $passengerCount,
        string $seatClass
    ): Collection;
}
