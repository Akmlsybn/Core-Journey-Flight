<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('passengers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')
                ->constrained('bookings')
                ->onDelete('cascade');
            $table->string('name');
            $table->string('id_number', 16); // NIK (16 digits)
            $table->enum('seat_class', ['economy', 'business', 'first_class']);
            $table->timestamps();

            // Index untuk pencarian cepat berdasarkan booking
            $table->index('booking_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('passengers');
    }
};
