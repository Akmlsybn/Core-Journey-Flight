# 🔧 Implementation Examples & Best Practices

## 1. Controller Integration Example

### Booking Controller

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingRequest;
use App\Services\BookingService;
use App\Repositories\BookingRepository;
use Illuminate\Http\JsonResponse;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService,
        private readonly BookingRepository $bookingRepository
    ) {
    }

    /**
     * Store a new booking
     * POST /api/bookings
     */
    public function store(BookingRequest $request): JsonResponse
    {
        try {
            // Validated data dari request
            $validated = $request->validated();

            // Create booking dengan passengers atomik
            $booking = $this->bookingService->createBooking($validated);

            return response()->json([
                'success' => true,
                'message' => 'Booking berhasil dibuat',
                'data' => [
                    'booking_id' => $booking->id,
                    'booking_code' => $booking->booking_code,
                    'total_passengers' => $booking->total_passengers,
                    'total_price' => $booking->total_price,
                    'status' => $booking->status,
                    'passengers' => $booking->passengers->map(fn ($p) => [
                        'id' => $p->id,
                        'name' => $p->name,
                        'id_number' => $p->id_number,
                        'seat_class' => $p->seat_class,
                    ]),
                    'flight' => [
                        'flight_number' => $booking->flightSchedule->flight_number,
                        'origin' => $booking->flightSchedule->origin,
                        'destination' => $booking->flightSchedule->destination,
                        'departure_date' => $booking->flightSchedule->departure_date,
                    ],
                ],
            ], 201);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi input gagal',
                'error' => $e->getMessage(),
            ], 422);

        } catch (\Exception $e) {
            // Kursi tidak tersedia atau error lainnya
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 409);
        }
    }

    /**
     * Show booking detail
     * GET /api/bookings/{code}
     */
    public function show(string $bookingCode): JsonResponse
    {
        $booking = $this->bookingRepository->getBookingByCode($bookingCode);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $booking->id,
                'booking_code' => $booking->booking_code,
                'status' => $booking->status,
                'total_passengers' => $booking->total_passengers,
                'total_price' => $booking->total_price,
                'passengers' => $booking->passengers->toArray(),
                'flight' => $booking->flightSchedule->load('airline')->toArray(),
                'tickets' => $booking->tickets->toArray(),
            ],
        ]);
    }

    /**
     * Check seat availability
     * GET /api/bookings/check-availability
     */
    public function checkAvailability(): JsonResponse
    {
        $flightId = request()->integer('flight_id');
        $seatClass = request()->string('seat_class', 'economy');
        $passengerCount = request()->integer('passenger_count', 1);

        try {
            $isAvailable = $this->bookingRepository->checkSeatAvailability(
                $flightId,
                $seatClass,
                $passengerCount
            );

            if (!$isAvailable) {
                return response()->json([
                    'available' => false,
                    'message' => 'Kursi tidak tersedia',
                ], 409);
            }

            $details = $this->bookingRepository->getSeatClassDetails($flightId, $seatClass);

            return response()->json([
                'available' => true,
                'available_seats' => $details->available_seats,
                'class_price' => $details->class_price,
                'total_for_passengers' => $details->class_price * $passengerCount,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Confirm payment
     * POST /api/bookings/{id}/confirm-payment
     */
    public function confirmPayment(int $bookingId): JsonResponse
    {
        $paymentStatus = request()->string('payment_status');
        $paymentMethod = request()->string('payment_method');

        try {
            $success = $this->bookingService->confirmPayment(
                $bookingId,
                $paymentStatus,
                $paymentMethod
            );

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran gagal diproses',
                ], 422);
            }

            $booking = $this->bookingRepository->getBookingWithRelations($bookingId);

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil',
                'data' => [
                    'booking_code' => $booking->booking_code,
                    'status' => $booking->status,
                    'tickets' => $booking->tickets->map(fn ($t) => [
                        'ticket_number' => $t->ticket_number,
                        'passenger_name' => $t->passenger_name,
                    ]),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
```

---

## 2. Form Request Validation

### BookingRequest

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'flight_schedule_id' => 'required|integer|exists:flight_schedules,id',
            'passengers' => 'required|array|min:1|max:9',
            'passengers.*.name' => 'required|string|max:255',
            'passengers.*.id_number' => 'required|string|size:16|regex:/^[0-9]{16}$/',
            'passengers.*.seat_class' => 'required|in:economy,business,first_class',
            'ancillary_services' => 'nullable|array',
            'ancillary_services.*' => 'string|in:travel_insurance,extra_baggage,seat_selection,upgrade_lounge',
        ];
    }

    public function messages(): array
    {
        return [
            'passengers.required' => 'Minimal harus ada 1 penumpang',
            'passengers.max' => 'Maksimal 9 penumpang per booking',
            'passengers.*.id_number.size' => 'ID number harus 16 digit',
            'passengers.*.id_number.regex' => 'ID number harus angka semua',
        ];
    }
}
```

---

## 3. Route Definition

### web.php atau api.php

```php
<?php

use App\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Route;

// Booking routes
Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
Route::get('/bookings/{code}', [BookingController::class, 'show'])->name('bookings.show');
Route::get('/bookings/check-availability', [BookingController::class, 'checkAvailability'])->name('bookings.checkAvailability');
Route::post('/bookings/{id}/confirm-payment', [BookingController::class, 'confirmPayment'])->name('bookings.confirmPayment');
```

---

## 4. Unit Test Examples

### BookingRepositoryTest

```php
<?php

namespace Tests\Unit\Repositories;

use App\Models\Booking;
use App\Models\FlightSchedule;
use App\Models\FlightSeatClass;
use App\Repositories\BookingRepository;
use Tests\TestCase;

class BookingRepositoryTest extends TestCase
{
    private BookingRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new BookingRepository();
    }

    public function test_check_seat_availability_when_seats_available()
    {
        $flight = FlightSchedule::factory()->create();
        FlightSeatClass::factory()->create([
            'flight_schedule_id' => $flight->id,
            'seat_class' => 'business',
            'available_seats' => 5,
        ]);

        $result = $this->repository->checkSeatAvailability(
            $flight->id,
            'business',
            3
        );

        $this->assertTrue($result);
    }

    public function test_check_seat_availability_when_seats_not_available()
    {
        $flight = FlightSchedule::factory()->create();
        FlightSeatClass::factory()->create([
            'flight_schedule_id' => $flight->id,
            'seat_class' => 'business',
            'available_seats' => 2,
        ]);

        $result = $this->repository->checkSeatAvailability(
            $flight->id,
            'business',
            5
        );

        $this->assertFalse($result);
    }

    public function test_create_booking_with_passengers_atomically()
    {
        $flight = FlightSchedule::factory()->create();
        $seatClass = FlightSeatClass::factory()->create([
            'flight_schedule_id' => $flight->id,
            'available_seats' => 10,
        ]);

        $bookingData = [
            'flight_schedule_id' => $flight->id,
            'booking_code' => 'BK-TEST-001',
            'total_passengers' => 2,
            'status' => 'pending',
            'total_price' => 5000000,
        ];

        $passengersData = [
            [
                'name' => 'John Doe',
                'id_number' => '1234567890123456',
                'seat_class' => 'economy',
            ],
            [
                'name' => 'Jane Doe',
                'id_number' => '6543210987654321',
                'seat_class' => 'economy',
            ],
        ];

        $booking = $this->repository->createBookingWithPassengers(
            $bookingData,
            $passengersData
        );

        // Verify booking created
        $this->assertInstanceOf(Booking::class, $booking);
        $this->assertEquals('BK-TEST-001', $booking->booking_code);

        // Verify passengers created
        $this->assertCount(2, $booking->passengers);
        $this->assertEquals('John Doe', $booking->passengers[0]->name);

        // Verify seats decreased
        $updated = FlightSeatClass::find($seatClass->id);
        $this->assertEquals(8, $updated->available_seats);
    }

    public function test_create_booking_rollbacks_on_error()
    {
        $flight = FlightSchedule::factory()->create();
        FlightSeatClass::factory()->create([
            'flight_schedule_id' => $flight->id,
            'available_seats' => 10,
        ]);

        $bookingData = [
            'flight_schedule_id' => $flight->id,
            'booking_code' => 'BK-TEST-002',
            'total_passengers' => 1,
            'status' => 'pending',
            'total_price' => 2500000,
        ];

        $passengersData = [
            [
                'name' => 'John',
                'id_number' => '123456789012345', // Invalid: 15 digits
                'seat_class' => 'economy',
            ],
        ];

        try {
            $this->repository->createBookingWithPassengers(
                $bookingData,
                $passengersData
            );
        } catch (\Exception $e) {
            // Expected exception
        }

        // Verify nothing was created
        $this->assertDatabaseMissing('bookings', ['booking_code' => 'BK-TEST-002']);
    }

    public function test_get_seat_class_details()
    {
        $flight = FlightSchedule::factory()->create();
        FlightSeatClass::factory()->create([
            'flight_schedule_id' => $flight->id,
            'seat_class' => 'business',
            'available_seats' => 5,
            'class_price' => 2500000,
        ]);

        $details = $this->repository->getSeatClassDetails(
            $flight->id,
            'business'
        );

        $this->assertNotNull($details);
        $this->assertEquals(5, $details->available_seats);
        $this->assertEquals(2500000, $details->class_price);
    }
}
```

### BookingServiceTest

```php
<?php

namespace Tests\Unit\Services;

use App\Models\FlightSchedule;
use App\Models\FlightSeatClass;
use App\Repositories\BookingRepository;
use App\Services\BookingService;
use Tests\TestCase;

class BookingServiceTest extends TestCase
{
    private BookingService $service;
    private BookingRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new BookingRepository();
        $this->service = new BookingService($this->repository);
    }

    public function test_create_booking_validates_input()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('flight_schedule_id adalah required');

        $this->service->createBooking([]);
    }

    public function test_create_booking_with_valid_data()
    {
        $flight = FlightSchedule::factory()->create();
        FlightSeatClass::factory()->create([
            'flight_schedule_id' => $flight->id,
            'seat_class' => 'economy',
            'available_seats' => 10,
            'class_price' => 1500000,
        ]);

        $data = [
            'flight_schedule_id' => $flight->id,
            'passengers' => [
                [
                    'name' => 'Test Passenger',
                    'id_number' => '1234567890123456',
                    'seat_class' => 'economy',
                ],
            ],
        ];

        $booking = $this->service->createBooking($data);

        $this->assertEquals('pending', $booking->status);
        $this->assertEquals(1500000, $booking->total_price);
        $this->assertCount(1, $booking->passengers);
    }

    public function test_create_booking_with_ancillary_services()
    {
        $flight = FlightSchedule::factory()->create();
        FlightSeatClass::factory()->create([
            'flight_schedule_id' => $flight->id,
            'class_price' => 1500000,
            'available_seats' => 10,
        ]);

        $data = [
            'flight_schedule_id' => $flight->id,
            'passengers' => [
                [
                    'name' => 'Test',
                    'id_number' => '1234567890123456',
                    'seat_class' => 'economy',
                ],
            ],
            'ancillary_services' => ['travel_insurance', 'extra_baggage'],
        ];

        $booking = $this->service->createBooking($data);

        // Base price: 1500000
        // Insurance: 50000
        // Baggage: 100000
        // Total: 1650000
        $this->assertEquals(1650000, $booking->total_price);
    }

    public function test_create_booking_fails_when_seats_unavailable()
    {
        $flight = FlightSchedule::factory()->create();
        FlightSeatClass::factory()->create([
            'flight_schedule_id' => $flight->id,
            'available_seats' => 2,
        ]);

        $data = [
            'flight_schedule_id' => $flight->id,
            'passengers' => array_map(
                fn ($i) => [
                    'name' => "Passenger {$i}",
                    'id_number' => str_pad((string)$i, 16, '0'),
                    'seat_class' => 'economy',
                ],
                range(1, 3)
            ),
        ];

        $this->expectException(\Exception::class);
        $this->service->createBooking($data);
    }
}
```

---

## 5. API Usage Examples

### Create Booking (cURL)

```bash
curl -X POST http://localhost:8000/api/bookings \
  -H "Content-Type: application/json" \
  -d '{
    "flight_schedule_id": 1,
    "passengers": [
      {
        "name": "John Doe",
        "id_number": "1234567890123456",
        "seat_class": "business"
      },
      {
        "name": "Jane Smith",
        "id_number": "6543210987654321",
        "seat_class": "business"
      }
    ],
    "ancillary_services": ["travel_insurance"]
  }'
```

### Check Seat Availability (cURL)

```bash
curl -X GET "http://localhost:8000/api/bookings/check-availability?flight_id=1&seat_class=business&passenger_count=2"
```

### Confirm Payment (cURL)

```bash
curl -X POST http://localhost:8000/api/bookings/1/confirm-payment \
  -H "Content-Type: application/json" \
  -d '{
    "payment_status": "successful",
    "payment_method": "credit_card"
  }'
```

---

## 6. Database Indexes untuk Performa

### Migration untuk Indexes

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Index untuk pengecekan ketersediaan kursi
        Schema::table('flight_seat_classes', function (Blueprint $table) {
            $table->index(['flight_schedule_id', 'seat_class', 'available_seats']);
        });

        // Index untuk query booking by code
        Schema::table('bookings', function (Blueprint $table) {
            $table->index('booking_code');
        });

        // Index untuk query passenger by booking
        Schema::table('passengers', function (Blueprint $table) {
            $table->index('booking_id');
        });
    }

    public function down(): void
    {
        Schema::table('flight_seat_classes', function (Blueprint $table) {
            $table->dropIndex(['flight_schedule_id', 'seat_class', 'available_seats']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('idx_booking_code');
        });

        Schema::table('passengers', function (Blueprint $table) {
            $table->dropIndex('idx_passenger_booking');
        });
    }
};
```

---

## 7. Troubleshooting

### Problem: Seat count menjadi negative

**Cause:** Race condition pada concurrent requests

**Solution:** Gunakan pessimistic locking di repository

```php
$seatClass = FlightSeatClass::query()
    ->where('flight_schedule_id', $flightId)
    ->where('seat_class', $seatClass)
    ->lockForUpdate()  // Tambahkan lock
    ->first();

if (!$seatClass || $seatClass->available_seats < $count) {
    throw new \Exception('Kursi tidak tersedia');
}
```

### Problem: Transaction timeout

**Cause:** Query terlalu lambat dalam transaction

**Solution:** Optimize query dengan index dan eager loading

```php
// ❌ Slow (N+1 queries)
$bookings = Booking::all();
foreach ($bookings as $booking) {
    $flight = $booking->flightSchedule; // Query per row
}

// ✅ Fast (1 query)
$bookings = Booking::with('flightSchedule')->get();
```

---

**Dokumentasi praktis untuk implementasi Repository & Service**

Untuk pertanyaan lebih lanjut, lihat BOOKING_SERVICE_REPOSITORY_GUIDE.md atau testing documentation.
