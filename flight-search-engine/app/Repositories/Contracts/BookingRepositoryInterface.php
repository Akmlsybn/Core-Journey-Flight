<?php

namespace App\Repositories\Contracts;

use App\Models\Booking;
use Illuminate\Support\Collection;

interface BookingRepositoryInterface
{
    /**
     * Cek ketersediaan kursi untuk flight dan seat class tertentu.
     *
     * @param int $flightScheduleId Flight schedule ID
     * @param string $seatClass Seat class (economy, business, first_class)
     * @param int $passengerCount Jumlah penumpang yang dibutuhkan
     *
     * @return bool True jika kursi tersedia, false jika tidak
     */
    public function checkSeatAvailability(
        int $flightScheduleId,
        string $seatClass,
        int $passengerCount
    ): bool;

    /**
     * Dapatkan detail ketersediaan kursi untuk flight tertentu.
     *
     * @param int $flightScheduleId Flight schedule ID
     * @param string $seatClass Seat class
     *
     * @return object|null Object berisi {available_seats, class_price} atau null jika tidak ditemukan
     */
    public function getSeatClassDetails(int $flightScheduleId, string $seatClass): ?object;

    /**
     * Buat booking dengan detail penumpang secara atomik menggunakan database transaction.
     *
     * @param array $bookingData Data booking: flight_schedule_id, booking_code, total_passengers, status, total_price, ancillary_services
     * @param array $passengersData Array of passenger data: [['name' => '...', 'id_number' => '...', 'seat_class' => '...'], ...]
     *
     * @return Booking Booking yang baru dibuat dengan loaded passengers
     * @throws \Exception Jika transaksi gagal (akan di-rollback)
     */
    public function createBookingWithPassengers(array $bookingData, array $passengersData): Booking;

    /**
     * Dapatkan booking dengan relasi yang di-eager load.
     *
     * @param int $bookingId Booking ID
     *
     * @return Booking|null
     */
    public function getBookingWithRelations(int $bookingId): ?Booking;

    /**
     * Dapatkan booking berdasarkan booking code.
     *
     * @param string $bookingCode Booking code
     *
     * @return Booking|null
     */
    public function getBookingByCode(string $bookingCode): ?Booking;

    /**
     * Kurangi jumlah kursi yang tersedia setelah booking berhasil.
     *
     * @param int $flightScheduleId Flight schedule ID
     * @param string $seatClass Seat class
     * @param int $passengerCount Jumlah kursi yang dikurangi
     *
     * @return bool
     */
    public function decreaseAvailableSeats(
        int $flightScheduleId,
        string $seatClass,
        int $passengerCount
    ): bool;

    /**
     * Tambah kembali kursi saat booking dibatalkan.
     */
    public function increaseAvailableSeats(
        int $flightScheduleId,
        string $seatClass,
        int $passengerCount
    ): bool;

    /**
     * Update status booking.
     *
     * @param int $bookingId Booking ID
     * @param string $status Status baru
     *
     * @return bool
     */
    public function updateBookingStatus(int $bookingId, string $status, ?string $paymentMethod = null): bool;
}
