<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Ticket;
use App\Repositories\BookingRepository;
use Illuminate\Support\Str;

class BookingService
{
    public function __construct(
        private readonly BookingRepository $bookingRepository
    ) {
    }

    /**
     * Buat booking baru dengan detail penumpang secara atomik.
     * 
     * @param array $data Data booking dengan struktur:
     *                     [
     *                       'flight_schedule_id' => int,
     *                       'passenger_count' => int,
     *                       'ancillary_services' => array|null,
     *                       'passengers' => [
     *                         ['name' => '...', 'id_number' => '...', 'seat_class' => '...'],
     *                         ...
     *                       ]
     *                     ]
     * 
     * @return Booking Booking yang baru dibuat dengan passengers
     * @throws \InvalidArgumentException Jika input tidak valid
     * @throws \Exception Jika seat tidak tersedia atau error database
     */
    public function createBooking(array $data): Booking
    {
        // Validasi input
        $this->validateBookingData($data);

        // Ekstrak data
        $flightScheduleId = (int) $data['flight_schedule_id'];
        $passengersData = $data['passengers'] ?? [];
        $passengerCount = count($passengersData);

        // Pengecekan ketersediaan kursi
        if ($passengerCount <= 0) {
            throw new \InvalidArgumentException('Minimal harus ada 1 penumpang');
        }

        // Ambil seat class dari penumpang pertama (asumsi semua penumpang kelas yang sama
        // atau jika berbeda, divalidasi di level controller/request)
        $seatClass = $passengersData[0]['seat_class'] ?? 'economy';

        // Cek ketersediaan kursi
        if (!$this->bookingRepository->checkSeatAvailability($flightScheduleId, $seatClass, $passengerCount)) {
            throw new \Exception(
                "Maaf, kursi {$seatClass} tidak tersedia untuk {$passengerCount} penumpang "
                . "di penerbangan ini. Silakan pilih penerbangan atau kelas kursi lain."
            );
        }

        // Generate booking code
        $bookingCode = $this->generateBookingCode();

        // Hitung total harga
        $totalPrice = $this->calculateTotalPrice($data);

        // Persiapan data booking
        $bookingData = [
            'flight_schedule_id' => $flightScheduleId,
            'booking_code' => $bookingCode,
            'total_passengers' => $passengerCount,
            'ancillary_services' => $data['ancillary_services'] ?? [],
            'status' => 'pending',
            'total_price' => $totalPrice,
        ];

        // Buat booking + passengers secara atomik (dalam satu transaction)
        return $this->bookingRepository->createBookingWithPassengers($bookingData, $passengersData);
    }

    /**
     * Konfirmasi pembayaran dan update status booking.
     */
    public function confirmPayment(int $bookingId, string $paymentStatus, string $paymentMethod): bool
    {
        $booking = $this->bookingRepository->getBookingWithRelations($bookingId);

        if (!$booking || $booking->status !== 'pending' || $paymentStatus !== 'successful') {
            return false;
        }

        // Update status menjadi paid
        $this->bookingRepository->updateBookingStatus($bookingId, 'paid');

        // Reload booking dan generate ticket
        $booking = $this->bookingRepository->getBookingWithRelations($bookingId);
        $this->generateTicketsForPassengers($booking);

        return true;
    }

    /**
     * Generate tickets untuk setiap penumpang dalam booking.
     */
    private function generateTicketsForPassengers(Booking $booking): void
    {
        // Cegah duplicate tickets
        if ($booking->tickets()->exists()) {
            return;
        }

        // Generate ticket untuk setiap penumpang
        foreach ($booking->passengers as $passenger) {
            Ticket::create([
                'booking_id' => $booking->id,
                'ticket_number' => $this->generateTicketNumber(),
                'passenger_name' => $passenger->name,
                'seat_number' => $this->assignSeatNumber($passenger->seat_class),
            ]);
        }
    }

    /**
     * Generate unique booking code.
     */
    private function generateBookingCode(): string
    {
        do {
            // Format: BK + YYYYMMDD + 6 random chars
            $code = 'BK' . now()->format('YmdHis') . strtoupper(Str::random(4));
        } while (Booking::where('booking_code', $code)->exists());

        return $code;
    }

    /**
     * Generate unique ticket number.
     */
    private function generateTicketNumber(): string
    {
        do {
            $number = 'TK' . strtoupper(Str::random(12));
        } while (Ticket::where('ticket_number', $number)->exists());

        return $number;
    }

    /**
     * Assign seat number berdasarkan seat class.
     */
    private function assignSeatNumber(string $seatClass): string
    {
        $prefix = match ($seatClass) {
            'economy' => 'E',
            'business' => 'B',
            'first_class' => 'F',
            default => 'E',
        };

        return $prefix . rand(1, 200);
    }

    /**
     * Hitung total harga booking berdasarkan flight, seat class, dan layanan tambahan.
     */
    private function calculateTotalPrice(array $data): float
    {
        $flightScheduleId = (int) $data['flight_schedule_id'];
        $passengersData = $data['passengers'] ?? [];
        $passengerCount = count($passengersData);
        $seatClass = $passengersData[0]['seat_class'] ?? 'economy';

        // Dapatkan harga kelas dari database
        $seatClassDetails = $this->bookingRepository->getSeatClassDetails($flightScheduleId, $seatClass);
        
        if (!$seatClassDetails) {
            // Fallback jika tidak ditemukan (error handling)
            throw new \Exception('Seat class tidak ditemukan untuk penerbangan ini');
        }

        // Hitung harga dasar = class_price * jumlah penumpang
        $basePrice = (float) $seatClassDetails->class_price * $passengerCount;

        // Tambahkan layanan tambahan
        $ancillaryPrice = 0;
        if (isset($data['ancillary_services']) && is_array($data['ancillary_services'])) {
            foreach ($data['ancillary_services'] as $service) {
                $ancillaryPrice += match ($service) {
                    'travel_insurance' => 50000,
                    'extra_baggage' => 100000,
                    'seat_selection' => 25000,
                    'upgrade_lounge' => 75000,
                    default => 0,
                };
            }
        }

        return $basePrice + $ancillaryPrice;
    }

    /**
     * Validasi data booking.
     */
    private function validateBookingData(array $data): void
    {
        if (empty($data['flight_schedule_id'])) {
            throw new \InvalidArgumentException('flight_schedule_id adalah required');
        }

        if (empty($data['passengers']) || !is_array($data['passengers'])) {
            throw new \InvalidArgumentException('passengers harus berupa array dan tidak boleh kosong');
        }

        foreach ($data['passengers'] as $index => $passenger) {
            if (empty($passenger['name'])) {
                throw new \InvalidArgumentException("Penumpang {$index}: name adalah required");
            }
            if (empty($passenger['id_number'])) {
                throw new \InvalidArgumentException("Penumpang {$index}: id_number adalah required");
            }
            if (strlen((string) $passenger['id_number']) !== 16) {
                throw new \InvalidArgumentException("Penumpang {$index}: id_number harus 16 digit");
            }
            if (empty($passenger['seat_class'])) {
                throw new \InvalidArgumentException("Penumpang {$index}: seat_class adalah required");
            }
            if (!in_array($passenger['seat_class'], ['economy', 'business', 'first_class'])) {
                throw new \InvalidArgumentException("Penumpang {$index}: seat_class tidak valid");
            }
        }
    }
}
