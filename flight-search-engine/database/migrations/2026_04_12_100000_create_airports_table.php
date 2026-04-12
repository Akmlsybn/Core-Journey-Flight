<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('airports', function (Blueprint $table) {
            $table->id();
            $table->string('airport_code', 10)->unique();
            $table->string('airport_name');
            $table->string('city_name');
            $table->string('country_name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('airports');
    }
};
