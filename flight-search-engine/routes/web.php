<?php

use App\Http\Controllers\FlightSearchController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/flights/search');

Route::get('/flights/search', [FlightSearchController::class, 'search'])->name('flights.search');
Route::get('/flights/results', [FlightSearchController::class, 'results'])->name('flights.results');
Route::get('/flights/calendar-prices', [FlightSearchController::class, 'calendarPrices'])->name('flights.calendar-prices');
