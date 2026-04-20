<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('booking_confirmed') }}</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.5;">
    <h2>{{ __('booking_confirmed') }}</h2>
    <p>{{ __('booking_success_desc') }}</p>

    <p><strong>{{ __('booking_code') }}:</strong> {{ $booking->booking_code }}</p>
    <p><strong>{{ __('flight_number') }}:</strong> {{ $booking->flightSchedule?->flight_number }}</p>
    <p><strong>{{ __('payment_status') }}:</strong> {{ __('paid') }}</p>

    <p>{{ __('download_e_ticket') }} terlampir pada email ini dalam format PDF.</p>
</body>
</html>
