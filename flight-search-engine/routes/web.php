<?php

use App\Http\Controllers\FlightSearchController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/flights/search');

Route::get('/flights/search', [FlightSearchController::class, 'search'])->name('flights.search');
Route::get('/flights/available-dates', [FlightSearchController::class, 'availableDates'])->name('flights.available-dates');
Route::get('/flights/results', [FlightSearchController::class, 'results'])->name('flights.results');
