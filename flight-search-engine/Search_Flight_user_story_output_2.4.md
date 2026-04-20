## 1. Summary Table
| **Prompt 1** | Berdasarkan file skills.md dan arsitektur.md yang ada di repositori ini, tolong buatkan Migration dan Model Laravel untuk entitas Booking dan Passenger.Ketentuan:

Tabel bookings harus menyimpan data: flight_id (foreign key), total_passengers, dan status.
Tabel passengers harus menyimpan data: booking_id (foreign key), name, id_number, dan seat_class.
Gunakan Atribut Utama yang disepakati: origin, destination, departure_date, passenger_count, dan seat_class.
Pastikan relasi antara Booking dan Passenger terdefinisi dengan benar di Model. |

| **Prompt 2** |oke lanjut karena saya sedang mengerjakan US 2.4 (Backend Core & Database) untuk sistem pencarian dan pemesanan tiket pesawat. Tolong buatkan Repository dan Service di Laravel untuk menangani:

Pengecekan ketersediaan kursi berdasarkan flight_id dan passenger_count.
Logika untuk menyimpan data pemesanan ke tabel bookings dan rincian penumpang ke tabel passengers secara atomik (menggunakan Database Transaction).
Pastikan kode mengikuti standar yang ada di folder app/Repositories dan app/Services jika sudah ada. |
| **Context File** | `skills.md`, `arsitektur.md`, `database/migrations/`, `app/Models/` |
| **Task** | Implementasi Migration, Model, Repository, dan Service untuk Backend Core (US 2.4) |
| **What Changed** | 1. Membuat migration dan model `Booking` & `Passenger`. <br> 2. Implementasi `BookingRepository` untuk manipulasi data DB. <br> 3. Implementasi `BookingService` untuk logika bisnis (cek kursi & transaksi). |
| **Commit Message** | `feat: implement backend core and database logic for US 2.4` |

## 2. Technical Details
### Input (@parameter)
- [cite_start]`flight_id` (int): ID dari jadwal penerbangan yang dipilih[cite: 123].
- [cite_start]`passenger_count` (int): Jumlah penumpang, minimal 1 dan maksimal 7[cite: 103].
- `passengers_data` (array): Berisi detail nama, nomor ID, dan kelas kursi setiap penumpang.

### Output (@return)
- [cite_start]`booking_id` (int/string): Mengembalikan ID pemesanan sebagai referensi[cite: 124].
- `status` (boolean): Status keberhasilan transaksi penyimpanan data.

### Validation Rules (Rules)
- [cite_start]`//validation`: Memastikan `flight_id` valid dan tersedia di sistem[cite: 124].
- `//validation`: Pengecekan sisa kursi harus mencukupi untuk jumlah `passenger_count`.
- `//validation`: Menggunakan Database Transaction untuk menjamin data tersimpan secara atomik.