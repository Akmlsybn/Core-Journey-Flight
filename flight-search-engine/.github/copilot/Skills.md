---
name: laravel-php-flight-booking
description: "Standard coding practices for Laravel MVC Flight Booking & E-Ticket - Kelompok 13."
---

# Laravel with PHP Best Practices (Flight Booking System)

Tujuan Anda adalah membantu kami menulis aplikasi Laravel yang berkualitas tinggi, bersih, dan mengikuti standar industri untuk fitur Pemesanan Tiket dan Penerbitan E-Ticket.

## Project Setup & Atribut Utama (Booking Journey)

- [cite_start]**Framework:** Gunakan Laravel 10+ dengan PHP 8.1+. [cite: 221]
- [cite_start]**Atribut Inti Pemesanan:** Setiap fitur pemesanan wajib menggunakan atribut berikut: [cite: 56, 91]
    - `booking_code` (Kode unik pemesanan)
    - [cite_start]`nik` (16 digit nomor identitas penumpang) [cite: 56, 91]
    - [cite_start]`full_name` (Nama lengkap sesuai identitas) [cite: 56, 91]
    - [cite_start]`payment_method` (Metode pembayaran yang dipilih) [cite: 56, 94]
    - [cite_start]`payment_status` (Status: pending, paid, cancelled) [cite: 56, 94]

## Arsitektur MVC & Dependency Injection

- **Controller:** Gunakan `app/Http/Controllers/BookingController`. [cite_start]Selalu gunakan _Dependency Injection_ melalui constructor untuk memanggil `BookingService`. [cite: 204, 221]
- [cite_start]**Service Layer:** Semua logika bisnis seperti perhitungan timer 30 menit (US 2.4) dan validasi ketersediaan kursi harus diletakkan di `app/Services/BookingService`. [cite: 195, 221]
- [cite_start]**Repository Pattern:** Gunakan `BookingRepository` untuk semua operasi database terkait tabel `bookings`, `passengers`, `payments`, dan `tickets`. [cite: 221]

## Web Layer & Validasi (US 2.3)

- **Form Request Validation:** Gunakan `BookingRequest` untuk memvalidasi input data penumpang. [cite_start]Wajib memastikan NIK terdiri dari 16 digit dan nama tidak kosong. [cite: 91, 221]
- [cite_start]**RESTful Endpoints:** Gunakan penamaan rute yang jelas: [cite: 221]
    - `POST /bookings` (Menyimpan data penumpang)
    - `GET /bookings/{id}/checkout` (Halaman pembayaran)
    - `GET /bookings/{id}/ticket` (Halaman E-Ticket)

## Data Layer & Integrity

- **Database Transactions:** Gunakan `DB::transaction()` saat menyimpan data pemesanan (US 2.2) dan data penumpang (US 2.3) secara bersamaan untuk menjaga integritas data.
- [cite_start]**Mass Assignment:** Definisikan atribut di `$fillable` pada model `Booking`, `Passenger`, `Payment`, dan `Ticket`. [cite: 221]
- [cite_start]**Casting:** Pastikan `payment_expired_at` di-_cast_ sebagai `datetime` agar logika _countdown_ 30 menit akurat. [cite: 94, 221]

## View & UI (Blade Templates)

- [cite_start]**Directives:** Manfaatkan `@error` untuk menandai field identitas yang salah dan `@old` agar user tidak perlu mengetik ulang nama penumpang jika validasi gagal. [cite: 221]
- [cite_start]**Timer Component:** Gunakan JavaScript sederhana (atau library) untuk menampilkan sisa waktu pembayaran 30 menit secara real-time di halaman checkout. [cite: 56, 94]

## Coding Standards

- [cite_start]**Strict Types:** Wajib menggunakan `declare(strict_types=1);` di setiap file PHP baru. [cite: 221]
- [cite_start]**PSR-12:** Ikuti standar penamaan: [cite: 221]
    - Class: `PascalCase` (Contoh: `BookingService`)
    - Method: `camelCase` (Contoh: `generateTicketCode`)
    - DB/Variables: `snake_case` (Contoh: `ticket_code`)

## Logging & Output (US 2.5)

- [cite_start]**Logging:** Catat setiap transaksi pembayaran yang sukses menggunakan `Log::info()` untuk keperluan audit. [cite: 221]
- [cite_start]**Ticket Generation:** Gunakan library seperti `barryvdh/laravel-dompdf` untuk men-generate file PDF E-Ticket setelah pembayaran statusnya Lunas. [cite: 56, 97]
