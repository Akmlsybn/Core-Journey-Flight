# 📋 Summary: Booking & Passenger Implementation

## ✅ Files Created

### 1. **Models** (2 files)

#### `app/Models/Passenger.php` (NEW)
- Model untuk entitas Passenger
- Relasi: `belongsTo(Booking)`
- Fillable: `booking_id`, `name`, `id_number`, `seat_class`

#### `app/Models/Booking.php` (UPDATE)
- Updated dengan relasi: `hasMany(Passenger)`
- Fillable fields: `flight_schedule_id`, `booking_code`, `total_passengers`, `ancillary_services`, `status`, `total_price`, `paid_at`
- Relasi lengkap:
  - `flightSchedule()` - BelongsTo
  - `passengers()` - HasMany
  - `tickets()` - HasMany

---

### 2. **Migrations** (3 files)

| File | Timestamp | Fungsi |
|------|-----------|--------|
| `2026_04_20_000000_create_passengers_table.php` | 1st | Membuat tabel passengers |
| `2026_04_20_100000_migrate_bookings_data_to_passengers.php` | 2nd | Migrasi data dari bookings |
| `2026_04_20_200000_refactor_bookings_table.php` | 3rd | Cleanup kolom dari bookings |

**⚠️ Important:** Migrations akan berjalan otomatis dalam urutan yang benar saat Anda menjalankan `php artisan migrate`

---

### 3. **Documentation** (1 file)

#### `BOOKING_PASSENGER_GUIDE.md` (NEW)
Dokumentasi lengkap yang mencakup:
- ERD diagram
- Struktur tabel detail
- Model relationships
- Usage examples
- Validasi data
- Best practices

---

## 🎯 Requirements Checklist

| Requirement | Status | Detail |
|-------------|--------|--------|
| ✅ Tabel bookings: flight_id | ✓ | `flight_schedule_id` (FK) |
| ✅ Tabel bookings: total_passengers | ✓ | Kolom `total_passengers` (integer) |
| ✅ Tabel bookings: status | ✓ | Enum: `pending`, `paid`, `cancelled` |
| ✅ Tabel passengers: booking_id | ✓ | Foreign key dengan cascade delete |
| ✅ Tabel passengers: name | ✓ | String type |
| ✅ Tabel passengers: id_number | ✓ | String(16) untuk NIK |
| ✅ Tabel passengers: seat_class | ✓ | Enum: `economy`, `business`, `first_class` |
| ✅ Main attributes: origin | ✓ | Via flightSchedule.origin |
| ✅ Main attributes: destination | ✓ | Via flightSchedule.destination |
| ✅ Main attributes: departure_date | ✓ | Via flightSchedule.departure_date |
| ✅ Main attributes: passenger_count | ✓ | booking.total_passengers |
| ✅ Main attributes: seat_class | ✓ | passengers.seat_class |
| ✅ Relasi Booking-Passenger | ✓ | `hasMany()` dan `belongsTo()` |

---

## 🚀 How to Use

### Step 1: Run Migrations
```bash
cd c:\Users\mhmmd\Core-Journey-Flight\flight-search-engine
php artisan migrate
```

### Step 2: Create Booking with Passengers
```php
use App\Models\Booking;

$booking = Booking::create([
    'flight_schedule_id' => 1,
    'booking_code' => 'BK-20260420-001',
    'total_passengers' => 2,
    'status' => 'pending',
    'total_price' => 7500000,
]);

// Add passengers
$booking->passengers()->create([
    'name' => 'John Doe',
    'id_number' => '1234567890123456',
    'seat_class' => 'business',
]);

$booking->passengers()->create([
    'name' => 'Jane Doe',
    'id_number' => '6543210987654321',
    'seat_class' => 'economy',
]);
```

### Step 3: Query with Relationships
```php
// Get booking with all passengers and flight details
$booking = Booking::with('passengers', 'flightSchedule')->find(1);

echo $booking->flightSchedule->origin;        // CGK
echo $booking->flightSchedule->destination;   // DPS
echo $booking->total_passengers;              // 2

foreach ($booking->passengers as $passenger) {
    echo $passenger->name . ' - ' . $passenger->seat_class;
}
```

---

## 📊 Table Structure

### bookings (Main Table)
```
id (PK) | flight_schedule_id (FK) | booking_code (UK) | total_passengers | 
status | total_price | ancillary_services | paid_at | created_at | updated_at
```

### passengers (Detail Table)
```
id (PK) | booking_id (FK) | name | id_number(16) | seat_class | created_at | updated_at
```

---

## 🔗 Database Relationships Visualization

```
flight_schedules
       |
      1|
       |
       |---* bookings
              |
              |---1 booking_code (unique identifier per booking)  
              |---N passengers (multiple passengers per booking)
              |
              |---* tickets
```

---

## 📝 Notes

1. **Migration Order**: Automatic - tidak perlu diatur manual
2. **Cascade Delete**: Menghapus booking akan otomatis menghapus passengers
3. **Data Integrity**: Gunakan transactions saat create booking + passengers
4. **Validation**: Selalu validate total_passengers = count of passengers records
5. **Backward Compatibility**: Migration 000002 akan migrasi data existing dari bookings ke passengers

---

## 🔍 File Locations

```
project/
├── app/
│   └── Models/
│       ├── Booking.php (UPDATED)
│       ├── Passenger.php (NEW)
│       ├── FlightSchedule.php
│       ├── Ticket.php
│       └── ...
├── database/
│   └── migrations/
│       ├── 2026_04_20_000000_create_passengers_table.php (NEW)
│       ├── 2026_04_20_100000_migrate_bookings_data_to_passengers.php (NEW)
│       ├── 2026_04_20_200000_refactor_bookings_table.php (NEW)
│       ├── 2026_04_19_140855_create_bookings_table.php (EXISTING)
│       └── ...
├── BOOKING_PASSENGER_GUIDE.md (NEW)
└── ...
```

---

## ✨ Arsitektur Sesuai Specification

✅ Semua requirements telah diimplementasikan sesuai dengan:
- `ARCHITECTURE.md` - ERD dan database design
- User requirements - Booking & Passenger entities
- Laravel best practices - Relations, migrations, models

---

**Created on:** April 20, 2026  
**Status:** Ready for testing and deployment
