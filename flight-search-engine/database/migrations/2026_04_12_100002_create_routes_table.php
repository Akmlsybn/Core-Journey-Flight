<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('origin_id')->constrained('airports')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('destination_id')->constrained('airports')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('route_code');
            $table->unsignedInteger('distance_km');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
