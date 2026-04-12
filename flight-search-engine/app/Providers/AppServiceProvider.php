<?php

namespace App\Providers;

use App\Repositories\Contracts\FlightRepositoryInterface;
use App\Repositories\FlightRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(FlightRepositoryInterface::class, FlightRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
