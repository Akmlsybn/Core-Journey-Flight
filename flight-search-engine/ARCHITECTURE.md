# Arsitektur Database & Alur Data (User Journey 2: Booking System)

Dokumen ini menjelaskan alur data dari permintaan HTTP hingga basis data untuk satu alur utama: **Proses Pemesanan/Booking (User Journey 2)**. Arsitektur ini merupakan pengembangan dari fitur pencarian sebelumnya dan tetap mengadopsi pola **MVC + Repository + Service Layer**.

## 1. Ringkasan Lapisan Arsitektur

Sistem memisahkan tanggung jawab setiap komponen agar kode tetap mudah diuji dan dikembangkan.

| Lapisan           | Peran                        | Komponen di Journey 2                       |
| :---------------- | :--------------------------- | :------------------------------------------ |
| **View**          | Antarmuka pengguna (Blade)   | Form Penumpang, Checkout, Success Page      |
| **Controller**    | Pengatur alur HTTP           | `BookingController`                         |
| **Form Request**  | Validasi input data (US 2.3) | `BookingRequest`                            |
| **Service Layer** | Logika bisnis & transaksi    | `BookingService`                            |
| **Repository**    | Operasi database (Eloquent)  | `BookingRepository`                         |
| **Model**         | Pemetaan tabel database      | `Booking`, `Passenger`, `Payment`, `Ticket` |

---

## 2. Desain Database (Pemesanan & E-Ticket)

Untuk mendukung US 2.2 hingga 2.5, sistem menambahkan 4 tabel baru yang berelasi dengan tabel jadwal penerbangan dari Journey sebelumnya.

- **`bookings`**: Menyimpan kode booking, total harga, dan status. Memiliki kolom `payment_expired_at` untuk mendukung **timer 30 menit (US 2.4)**.
- **`passengers`**: Menyimpan data identitas seperti **NIK (16 digit)** dan nama sesuai KTP (US 2.3).
- **`payments`**: Mencatat metode pembayaran dan status verifikasi transaksi (US 2.4).
- **`tickets`**: Diterbitkan secara otomatis setelah pembayaran lunas, menyimpan kode unik dan tautan PDF (US 2.5).

---

erDiagram
%% Tabel dari Journey 1
FLIGHT_SCHEDULES ||--o{ FLIGHT_SEAT_CLASSES : "memiliki"

    %% Hubungan ke Tabel Baru Journey 2
    FLIGHT_SCHEDULES ||--o{ BOOKINGS : "dipesan"
    FLIGHT_SEAT_CLASSES ||--o{ BOOKINGS : "dipilih"

    BOOKINGS ||--o{ PASSENGERS : "mencatat"
    BOOKINGS ||--o1 PAYMENTS : "memiliki"
    BOOKINGS ||--o1 TICKETS : "menerbitkan"

    BOOKINGS {
        string booking_code PK
        int flight_schedule_id FK
        decimal total_price
        enum status
        datetime payment_expired_at
    }

    PASSENGERS {
        int id PK
        int booking_id FK
        string nik
        string full_name
    }

    PAYMENTS {
        int id PK
        int booking_id FK
        string payment_method
        string status
    }

    TICKETS {
        int id PK
        int booking_id FK
        string ticket_code
    }

## 3. Alur Data (Sequence Diagram)

```mermaid
sequenceDiagram
    participant User
    participant Controller as BookingController
    participant Service as BookingService
    participant Repo as BookingRepository
    participant DB as Database

    User->>Controller: Pilih Penerbangan (US 2.2)
    Controller->>Service: Inisialisasi Booking
    Service->>Repo: Create Pending Booking
    Repo->>DB: INSERT into bookings

    User->>Controller: Input Data Penumpang (US 2.3)
    Controller->>Service: Simpan Data Penumpang
    Service->>Repo: savePassengers()
    Repo->>DB: INSERT into passengers

    Note over User, DB: Checkout & Timer 30 Menit (US 2.4)
    User->>Controller: Pilih Metode Pembayaran
    Controller->>Service: Start Payment Timer

    Note over User, DB: Konfirmasi & Tiket (US 2.5)
    User->>Controller: Konfirmasi Bayar
    Controller->>Service: Generate E-Ticket
    Service->>Repo: updateStatus & createTicket
    Repo->>DB: UPDATE bookings (Paid) & INSERT tickets
    Controller-->>User: Tampilkan E-Ticket
```
