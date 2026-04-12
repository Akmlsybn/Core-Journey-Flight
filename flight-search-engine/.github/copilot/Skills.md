---
name: laravel-php-flight-search
description: 'Standard coding practices for Laravel MVC Flight Search Engine - Kelompok 4.'
---

# Laravel with PHP Best Practices (Flight Search Engine)

Tujuan Anda adalah membantu kami menulis aplikasi Laravel yang berkualitas tinggi, bersih, dan mengikuti standar industri untuk fitur Pencarian Penerbangan.

## Project Setup & Atribut Utama (Shared)

- **Framework:** Gunakan Laravel 10+ dengan PHP 8.1+.
- **Atribut Wajib:** Setiap fitur pencarian wajib menggunakan atribut berikut:
  - `origin` (Kota Asal)
  - `destination` (Kota Tujuan)
  - `departure_date` (Tanggal Keberangkatan)
  - `passenger_count` (Jumlah Penumpang: min 1, max 7)
  - `seat_class` (Economy, Business, First Class)

## Arsitektur MVC & Dependency Injection

- **Controller:** Gunakan `app/Http/Controllers`. Selalu gunakan *Dependency Injection* melalui constructor atau method untuk memanggil Service Layer.
- **Immutability:** Di PHP, gunakan properti `readonly` (PHP 8.1+) pada DTO atau Service jika data tidak perlu diubah setelah inisialisasi.
- **Service Layer:** Pisahkan logika bisnis dari Controller ke dalam folder `app/Services` untuk menjaga prinsip *Single Responsibility*.

## Web Layer (Controllers & Routing)

- **RESTful Endpoints:** Gunakan penamaan rute yang deskriptif, misal: `/flights/search`.
- **Form Request Validation:** Jangan melakukan validasi di dalam Controller. Gunakan `php artisan make:request` dan letakkan aturan di `app/Http/Requests`.
- **Error Handling:** Gunakan blok `try-catch` di Controller dan lempar Exception ke `app/Exceptions/Handler.php` untuk respon JSON yang seragam.

## Data Layer (Models & Eloquent)

- **Mass Assignment:** Definisikan atribut di dalam `$fillable` pada Model `Flight`.
- **Local Scopes:** Gunakan *Local Scopes* untuk logika query pencarian agar tetap reusable, contoh: `scopeByRoute($query, $from, $to)`.
- **Casting:** Gunakan properti `$casts` di Model untuk memastikan `departure_date` adalah objek Carbon/Date dan `passenger_count` adalah integer.

## View (Blade Templates)

- **Components:** Gunakan `x-components` untuk elemen UI pencarian yang berulang (input field, dropdown kelas kursi).
- **Directives:** Manfaatkan `@error` untuk validasi pesan dan `@old` untuk mempertahankan input user saat form reload.

## Coding Standards

- **PSR-12:** Wajib mengikuti standar penamaan PSR-12.
- **Naming Convention:** - Variabel/Database: `snake_case`.
  - Method/Function: `camelCase`.
  - Class/Controller: `PascalCase`.
- **Type Hinting:** Selalu gunakan *Strict Types* `declare(strict_types=1);` dan definisikan return type pada setiap function.

## Logging & Testing

- **Logging:** Gunakan Facade `Log::info()` dengan context array untuk mencatat aktivitas pencarian user.
- **Testing:** Gunakan **Pest** atau **PHPUnit**. Fokus pada *Feature Test* untuk memastikan endpoint `/search` mengembalikan data yang benar sesuai kriteria.