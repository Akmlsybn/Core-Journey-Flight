<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flight_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('airline_id')->constrained('airlines')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('route_id')->constrained('routes')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('flight_number', 20);
            $table->string('origin', 10);
            $table->string('destination', 10);
            $table->date('departure_date');
            $table->time('departure_time');
            $table->time('arrival_time');
            $table->decimal('base_price', 12, 2);
            $table->string('flight_status', 32);
            $table->timestamps();

            $table->index(['departure_date', 'airline_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flight_schedules');
    }
};
