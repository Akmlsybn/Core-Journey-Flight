<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Refactor bookings table: remove passenger-specific fields (full_name, nik, seat_class)
     * yang sekarang harus disimpan di tabel passengers
     * 
     * IMPORTANT: Jangan jalankan migration ini sebelum data migration selesai!
     * Urutan yang benar:
     * 1. create_passengers_table
     * 2. migrate_bookings_data_to_passengers  
     * 3. refactor_bookings_table (ini)
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Hapus kolom yang seharusnya ada di passengers table
            if (Schema::hasColumn('bookings', 'full_name')) {
                $table->dropColumn('full_name');
            }
            if (Schema::hasColumn('bookings', 'nik')) {
                $table->dropColumn('nik');
            }
            if (Schema::hasColumn('bookings', 'seat_class')) {
                $table->dropColumn('seat_class');
            }
            
            // Rename passenger_count menjadi total_passengers untuk konsistensi
            if (Schema::hasColumn('bookings', 'passenger_count')) {
                $table->renameColumn('passenger_count', 'total_passengers');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Tambahkan kembali kolom yang dihapus
            if (!Schema::hasColumn('bookings', 'full_name')) {
                $table->string('full_name')->after('booking_code')->nullable();
            }
            if (!Schema::hasColumn('bookings', 'nik')) {
                $table->string('nik')->after('full_name')->nullable();
            }
            if (!Schema::hasColumn('bookings', 'seat_class')) {
                $table->enum('seat_class', ['economy', 'business', 'first_class'])
                    ->after('nik')->nullable();
            }
            
            // Rename total_passengers kembali ke passenger_count
            if (Schema::hasColumn('bookings', 'total_passengers')) {
                $table->renameColumn('total_passengers', 'passenger_count');
            }
        });
    }
};
