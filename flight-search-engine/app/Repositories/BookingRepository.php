<?php

namespace App\Repositories;

use App\Models\Booking;
use App\Models\FlightSeatClass;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Support\Facades\DB;

class BookingRepository implements BookingRepositoryInterface
{
    /**
     * Cek ketersediaan kursi untuk flight dan seat class tertentu.
     */
    public function checkSeatAvailability(
        int $flightScheduleId,
        string $seatClass,
        int $passengerCount
    ): bool {
        $seatAvailable = FlightSeatClass::query()
            ->where('flight_schedule_id', $flightScheduleId)
            ->where('seat_class', $seatClass)
            ->where('available_seats', '>=', $passengerCount)
            ->exists();

        return $seatAvailable;
    }

    /**
     * Dapatkan detail ketersediaan kursi untuk flight tertentu.
     */
    public function getSeatClassDetails(int $flightScheduleId, string $seatClass): ?object
    {
        return FlightSeatClass::query()
            ->where('flight_schedule_id', $flightScheduleId)
            ->where('seat_class', $seatClass)
            ->select('available_seats', 'class_price')
            ->first();
    }

    /**
     * Buat booking dengan detail penumpang secara atomik menggunakan database transaction.
     * 
     * @throws \Exception Jika ada error, transaction akan di-rollback otomatis
     */
    public function createBookingWithPassengers(array $bookingData, array $passengersData): Booking
    {
        return DB::transaction(function () use ($bookingData, $passengersData) {
            // 1. Create booking record
            $booking = Booking::create($bookingData);

            // 2. Create passenger records
            if (!empty($passengersData)) {
                foreach ($passengersData as $passengerData) {
                    $booking->passengers()->create([
                        'name' => $passengerData['name'],
                        'id_number' => $passengerData['id_number'],
                        'seat_class' => $passengerData['seat_class'],
                    ]);
                }
            }

            // 3. Decrease available seats for the seat class
            $this->decreaseAvailableSeats(
                $bookingData['flight_schedule_id'],
                $passengersData[0]['seat_class'] ?? 'economy',
                count($passengersData)
            );

            // 4. Reload dengan relasi
            return $booking->load('passengers', 'flightSchedule');
        });
    }

    /**
     * Dapatkan booking dengan relasi yang di-eager load.
     */
    public function getBookingWithRelations(int $bookingId): ?Booking
    {
        return Booking::query()
            ->with([
                'flightSchedule.airline',
                'flightSchedule.route.originAirport',
                'flightSchedule.route.destinationAirport',
                'passengers',
                'tickets',
            ])
            ->find($bookingId);
    }

    /**
     * Dapatkan booking berdasarkan booking code.
     */
    public function getBookingByCode(string $bookingCode): ?Booking
    {
        return Booking::query()
            ->with([
                'flightSchedule.airline',
                'flightSchedule.route.originAirport',
                'flightSchedule.route.destinationAirport',
                'passengers',
                'tickets',
            ])
            ->where('booking_code', $bookingCode)
            ->first();
    }

    /**
     * Kurangi jumlah kursi yang tersedia setelah booking berhasil.
     */
    public function decreaseAvailableSeats(
        int $flightScheduleId,
        string $seatClass,
        int $passengerCount
    ): bool {
        $affected = FlightSeatClass::query()
            ->where('flight_schedule_id', $flightScheduleId)
            ->where('seat_class', $seatClass)
            ->decrement('available_seats', $passengerCount);

        return $affected > 0;
    }

    /**
     * Update status booking.
     */
    public function updateBookingStatus(int $bookingId, string $status): bool
    {
        return Booking::query()
            ->where('id', $bookingId)
            ->update(['status' => $status]) > 0;
    }
}
