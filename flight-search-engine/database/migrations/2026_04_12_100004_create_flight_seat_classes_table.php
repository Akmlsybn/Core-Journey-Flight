<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flight_seat_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flight_schedule_id')->constrained('flight_schedules')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('seat_class', 32);
            $table->unsignedSmallInteger('seat_capacity');
            $table->unsignedSmallInteger('available_seats');
            $table->decimal('class_price', 12, 2);
            $table->timestamps();

            $table->unique(['flight_schedule_id', 'seat_class']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flight_seat_classes');
    }
};
