<?php

use App\Http\Controllers\FlightSearchController;
use App\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/flights/search');

Route::get('/flights/search', [FlightSearchController::class, 'search'])->name('flights.search');
Route::get('/flights/available-dates', [FlightSearchController::class, 'availableDates'])->name('flights.available-dates');
Route::get('/flights/results', [FlightSearchController::class, 'results'])->name('flights.results');
Route::get('/flights/{flightSchedule}', [BookingController::class, 'showFlight'])
	->whereNumber('flightSchedule')
	->name('flights.show');
Route::get('/bookings/{flightSchedule}/create', [BookingController::class, 'create'])
	->whereNumber('flightSchedule')
	->name('bookings.create');
Route::post('/bookings/{flightSchedule}', [BookingController::class, 'store'])
	->whereNumber('flightSchedule')
	->name('bookings.store');
Route::get('/bookings/{flightSchedule}/payment', [BookingController::class, 'payment'])
	->whereNumber('flightSchedule')
	->name('bookings.payment');
Route::post('/bookings/confirm-payment', [BookingController::class, 'confirmPayment'])->name('bookings.confirm-payment');
Route::get('/bookings/{booking}/download-eticket', [BookingController::class, 'downloadEticket'])
	->whereNumber('booking')
	->name('bookings.download-eticket');
Route::post('/bahasa/{lang}', [BookingController::class, 'switchLanguage'])->name('language.switch');
