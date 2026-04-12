# Panduan implementasi & pengujian — Pencarian penerbangan

Panduan ini membantu Anda **menjalankan aplikasi**, **menguji rute**, dan **memverifikasi fitur pencarian** secara manual maupun dengan perintah Artisan.

## Prasyarat

- PHP dan Composer terpasang (sesuai `composer.json`).
- Dependensi terpasang: `composer install` di folder proyek Laravel (`flight-search-engine`).
- File `.env` mengarah ke database yang valid; untuk cepat bisa memakai SQLite (lihat `.env.example`).
- Basis data terisi skema + seed demo:  
  `php artisan migrate:fresh --seed`

Seed `FlightSeeder` menyimpan jadwal contoh dengan tanggal **2026-05-15**, **2026-05-16**, dan **2026-05-17** (lihat `database/seeders/FlightSeeder.php`). Validasi penerbangan memakai **`after_or_equal:today`**, jadi pastikan tanggal sistem Anda **tidak melewati** tanggal-tanggal itu jika Anda ingin melihat hasil; atau sesuaikan tanggal di seeder / di `.env` `APP_TIMEZONE` bila perlu.

## Menjalankan server

Dari folder aplikasi Laravel:

```bash
php artisan serve
```

Buka browser ke `http://127.0.0.1:8000` — akan diarahkan ke **`/flights/search`**.

### Aset Tailwind (Vite)

Agar class Tailwind dari `resources/css/app.css` ter-*compile*:

```bash
npm install
npm run dev
```

Untuk build produksi:

```bash
npm run build
```

Jika Vite tidak jalan, layout tetap memuat `@vite`; pastikan salah satu dari `public/hot` (dev) atau `public/build/manifest.json` (build) ada agar stylesheet terhubung.

## Rute yang perlu Anda uji

| Metode | Path | Nama route | Perilaku |
|--------|------|------------|----------|
| GET | `/` | — | Redirect ke `/flights/search` |
| GET | `/flights/search` | `flights.search` | Form pencarian + daftar bandara |
| GET | `/flights/results` | `flights.results` | Hasil pencarian (butuh query valid) |

Cek daftar rute:

```bash
php artisan route:list --path=flights
```

## Skenario pengujian manual

### 1. Formulir valid

1. Buka `/flights/search`.
2. Pilih **asal** `CGK`, **tujuan** `DPS`, **tanggal** `2026-05-15` (sesuai seed).
3. Kirim form.

**Yang diharapkan:** URL berubah ke `/flights/results?origin=CGK&destination=DPS&departure_date=2026-05-15` (urutan parameter bisa beda), halaman hasil menampilkan minimal satu kartu jadwal (contoh: `GA-408`).

### 2. Tidak ada jadwal

1. Gunakan kombinasi valid misalnya `SUB` → `CGK` pada tanggal yang sama dengan seed jika tidak ada rute balik di seed.

**Yang diharapkan:** halaman hasil dengan pesan kosong (empty state), tanpa error aplikasi.

### 3. Validasi gagal

- **Asal = tujuan:** pilih bandara yang sama untuk asal dan tujuan → harus kembali ke `/flights/search` dengan pesan error.
- **Tanpa tanggal / tanggal lampau:** kosongkan tanggal atau pilih tanggal sebelum hari ini → error validasi.

`FlightSearchRequest` mengarahkan ulang ke `flights.search` pada gagal validasi (`getRedirectUrl()`), sehingga *deep link* ke `/flights/results` tanpa query tetap aman.

### 4. Uji perintah (opsional)

```bash
php artisan route:list
php artisan config:clear
php artisan view:clear
```

## Di mana mengubah perilaku

- **Aturan input / pesan error:** `app/Http/Requests/FlightSearchRequest.php`
- **Filter atau logika bisnis tambahan:** `app/Services/FlightSearchService.php`
- **Query database (join, scope, pagination):** `app/Repositories/FlightRepository.php`
- **Tampilan:** `resources/views/flights/search.blade.php`, `results.blade.php`

## Mengganti implementasi repository (uji unit)

Karena `FlightSearchService` bergantung pada **`FlightRepositoryInterface`**, untuk tes Anda bisa mendaftarkan *stub* di `AppServiceProvider` (atau `$this->app->instance(...)` di PHPUnit) tanpa mengubah service.

---

Untuk gambaran arsitektur lengkap, lihat [ARCHITECTURE.md](./ARCHITECTURE.md).
