<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Migrate data dari bookings (single passenger) ke passengers table (multi-passenger support)
     * 
     * IMPORTANT: Jalankan migration ini SEBELUM refactor_bookings_table untuk memastikan
     * data tidak hilang saat kolom dihapus.
     */
    public function up(): void
    {
        // Jika ada data dalam bookings table, pindahkan ke passengers table
        // Asumsi: setiap booking sebelumnya hanya memiliki 1 penumpang
        DB::statement('
            INSERT INTO passengers (booking_id, name, id_number, seat_class, created_at, updated_at)
            SELECT 
                b.id,
                COALESCE(b.full_name, "Unknown Passenger") as name,
                COALESCE(b.nik, "0000000000000000") as id_number,
                COALESCE(b.seat_class, "economy") as seat_class,
                b.created_at,
                b.updated_at
            FROM bookings b
            WHERE NOT EXISTS (
                SELECT 1 FROM passengers p WHERE p.booking_id = b.id
            )
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Untuk rollback, kita bisa kembalikan data dari passengers ke bookings
        // tapi ini hanya untuk single passenger bookings
        DB::statement('
            UPDATE bookings b
            SET b.full_name = (
                SELECT p.name FROM passengers p 
                WHERE p.booking_id = b.id 
                ORDER BY p.created_at 
                LIMIT 1
            ),
            b.nik = (
                SELECT p.id_number FROM passengers p 
                WHERE p.booking_id = b.id 
                ORDER BY p.created_at 
                LIMIT 1
            ),
            b.seat_class = (
                SELECT p.seat_class FROM passengers p 
                WHERE p.booking_id = b.id 
                ORDER BY p.created_at 
                LIMIT 1
            )
            WHERE EXISTS (
                SELECT 1 FROM passengers p WHERE p.booking_id = b.id
            )
        ');
    }
};
