<?php

use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('bookings:expire-pending', function (BookingService $bookingService): void {
    $expiredBookingIds = Booking::query()
        ->where('status', 'pending')
        ->whereNotNull('payment_expires_at')
        ->where('payment_expires_at', '<=', now())
        ->pluck('id');

    $cancelledCount = 0;

    foreach ($expiredBookingIds as $bookingId) {
        if ($bookingService->cancelExpiredBooking((int) $bookingId)) {
            $cancelledCount++;
        }
    }

    $this->info("Expired bookings cancelled: {$cancelledCount}");
})->purpose('Cancel expired pending bookings and release seats');

Schedule::command('bookings:expire-pending')->everyMinute();
