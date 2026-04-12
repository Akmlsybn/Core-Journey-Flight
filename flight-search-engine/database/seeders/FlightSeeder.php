<?php

namespace Database\Seeders;

use App\Models\Airline;
use App\Models\Airport;
use App\Models\FlightSchedule;
use App\Models\FlightSeatClass;
use App\Models\Route;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class FlightSeeder extends Seeder
{
    public function run(): void
    {
        $cgk = Airport::query()->create([
            'airport_code' => 'CGK',
            'airport_name' => 'Soekarno-Hatta International Airport',
            'city_name' => 'Jakarta',
            'country_name' => 'Indonesia',
        ]);

        $dps = Airport::query()->create([
            'airport_code' => 'DPS',
            'airport_name' => 'I Gusti Ngurah Rai International Airport',
            'city_name' => 'Denpasar',
            'country_name' => 'Indonesia',
        ]);

        $sub = Airport::query()->create([
            'airport_code' => 'SUB',
            'airport_name' => 'Juanda International Airport',
            'city_name' => 'Surabaya',
            'country_name' => 'Indonesia',
        ]);

        $ga = Airline::query()->create([
            'airline_code' => 'GA',
            'airline_name' => 'Garuda Indonesia',
        ]);

        $qz = Airline::query()->create([
            'airline_code' => 'QZ',
            'airline_name' => 'AirAsia Indonesia',
        ]);

        $routeCgkDps = Route::query()->create([
            'origin_id' => $cgk->id,
            'destination_id' => $dps->id,
            'route_code' => 'CGK-DPS',
            'distance_km' => 980,
        ]);

        $routeCgkSub = Route::query()->create([
            'origin_id' => $cgk->id,
            'destination_id' => $sub->id,
            'route_code' => 'CGK-SUB',
            'distance_km' => 690,
        ]);

        $routeSubDps = Route::query()->create([
            'origin_id' => $sub->id,
            'destination_id' => $dps->id,
            'route_code' => 'SUB-DPS',
            'distance_km' => 410,
        ]);

        $departureDate = Carbon::parse('2026-04-12');

        $schedule1 = FlightSchedule::query()->create([
            'airline_id' => $ga->id,
            'route_id' => $routeCgkDps->id,
            'flight_number' => 'GA-408',
            'origin' => $cgk->airport_code,
            'destination' => $dps->airport_code,
            'departure_date' => $departureDate,
            'departure_time' => '08:30:00',
            'arrival_time' => '11:05:00',
            'base_price' => 1850000,
            'flight_status' => 'scheduled',
        ]);

        FlightSeatClass::query()->create([
            'flight_schedule_id' => $schedule1->id,
            'seat_class' => 'economy',
            'seat_capacity' => 180,
            'available_seats' => 142,
            'class_price' => 1850000,
        ]);

        FlightSeatClass::query()->create([
            'flight_schedule_id' => $schedule1->id,
            'seat_class' => 'business',
            'seat_capacity' => 24,
            'available_seats' => 8,
            'class_price' => 5200000,
        ]);

        $schedule2 = FlightSchedule::query()->create([
            'airline_id' => $qz->id,
            'route_id' => $routeCgkSub->id,
            'flight_number' => 'QZ-7620',
            'origin' => $cgk->airport_code,
            'destination' => $sub->airport_code,
            'departure_date' => $departureDate->copy()->addDay(),
            'departure_time' => '14:15:00',
            'arrival_time' => '15:40:00',
            'base_price' => 650000,
            'flight_status' => 'scheduled',
        ]);

        FlightSeatClass::query()->create([
            'flight_schedule_id' => $schedule2->id,
            'seat_class' => 'economy',
            'seat_capacity' => 186,
            'available_seats' => 186,
            'class_price' => 650000,
        ]);

        $schedule3 = FlightSchedule::query()->create([
            'airline_id' => $ga->id,
            'route_id' => $routeSubDps->id,
            'flight_number' => 'GA-324',
            'origin' => $sub->airport_code,
            'destination' => $dps->airport_code,
            'departure_date' => $departureDate->copy()->addDays(2),
            'departure_time' => '09:00:00',
            'arrival_time' => '10:35:00',
            'base_price' => 920000,
            'flight_status' => 'boarding',
        ]);

        FlightSeatClass::query()->create([
            'flight_schedule_id' => $schedule3->id,
            'seat_class' => 'economy',
            'seat_capacity' => 162,
            'available_seats' => 55,
            'class_price' => 920000,
        ]);

        FlightSeatClass::query()->create([
            'flight_schedule_id' => $schedule3->id,
            'seat_class' => 'business',
            'seat_capacity' => 12,
            'available_seats' => 3,
            'class_price' => 2800000,
        ]);
    }
}
