<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('e_ticket') }} - {{ $booking->booking_code }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        .header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }
        .content {
            padding: 30px;
        }
        .section {
            margin-bottom: 30px;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
        }
        .section:last-child {
            border-bottom: none;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .info-item {
            margin-bottom: 15px;
        }
        .info-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }
        .ticket-number {
            background: #667eea;
            color: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 2px;
            margin: 20px 0;
        }
        .flight-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        .passenger-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #28a745;
        }
        .footer {
            background: #343a40;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 14px;
        }
        .barcode {
            text-align: center;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
            font-size: 18px;
            letter-spacing: 2px;
        }
        @media print {
            body { background: white; }
            .container { box-shadow: none; }
        }
    </style>
</head>
<body>
    @php
        $passengers = $booking->passengers->values();
        $tickets = $booking->tickets->values();
        $paymentMethodLabel = match ((string) $booking->payment_method) {
            'bank_transfer' => __('payment_bank_transfer'),
            'e_wallet' => __('payment_e_wallet'),
            'credit_card' => __('payment_credit_card'),
            default => '-',
        };
        $primaryTicket = $tickets->first();
    @endphp
    <div class="container">
        <div class="header">
            <h1>{{ __('e_ticket') }}</h1>
            <p>{{ __('electronic_ticket_confirmation') }}</p>
        </div>

        <div class="content">
            <div class="section">
                <div class="ticket-number">
                    {{ $primaryTicket?->ticket_number ?? 'N/A' }}
                </div>
            </div>

            <div class="section">
                <h2 class="section-title">{{ __('booking_information') }}</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">{{ __('booking_code') }}</div>
                        <div class="info-value">{{ $booking->booking_code }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">{{ __('status') }}</div>
                        <div class="info-value">{{ __('paid') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">{{ __('booking_date') }}</div>
                        <div class="info-value">{{ $booking->created_at->format('d M Y H:i') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">{{ __('payment_date') }}</div>
                        <div class="info-value">{{ optional($booking->paid_at)->format('d M Y H:i') }}</div>
                    </div>
                </div>
            </div>

            <div class="section">
                <h2 class="section-title">{{ __('passenger_information') }}</h2>
                <div class="passenger-info">
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">{{ __('full_name') }}</div>
                            <div class="info-value">{{ $passengers->first()?->name ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">{{ __('nik') }}</div>
                            <div class="info-value">{{ $passengers->first()?->id_number ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">{{ __('seat_class') }}</div>
                            <div class="info-value">{{ \Illuminate\Support\Str::of((string) ($passengers->first()?->seat_class ?? '-'))->replace('_', ' ')->title() }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">{{ __('seat_number') }}</div>
                            <div class="info-value">{{ $primaryTicket?->seat_number ?? 'TBA' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section">
                <h2 class="section-title">{{ __('passenger_list') }}</h2>
                @foreach ($passengers as $index => $passenger)
                    @php
                        $ticket = $tickets->get($index);
                    @endphp
                    <div class="info-grid" style="margin-bottom: 12px;">
                        <div class="info-item">
                            <div class="info-label">{{ __('passenger') }} #{{ $index + 1 }}</div>
                            <div class="info-value">{{ $passenger->name }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">{{ __('nik') }}</div>
                            <div class="info-value">{{ $passenger->id_number }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">{{ __('seat_class') }}</div>
                            <div class="info-value">{{ \Illuminate\Support\Str::of((string) $passenger->seat_class)->replace('_', ' ')->title() }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">{{ __('ticket_number') }}</div>
                            <div class="info-value">{{ $ticket?->ticket_number ?? 'N/A' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">{{ __('seat_number') }}</div>
                            <div class="info-value">{{ $ticket?->seat_number ?? 'TBA' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">{{ __('qr_code') }}</div>
                            <div class="info-value" style="font-family: 'Courier New', monospace;">{{ $ticket?->qr_code ?? '-' }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="section">
                <h2 class="section-title">{{ __('flight_details') }}</h2>
                <div class="flight-details">
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">{{ __('flight_number') }}</div>
                            <div class="info-value">{{ $booking->flightSchedule->flight_number }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">{{ __('airline') }}</div>
                            <div class="info-value">{{ $booking->flightSchedule->airline?->airline_name ?? __('airline_unavailable') }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">{{ __('route') }}</div>
                            <div class="info-value">{{ $booking->flightSchedule->origin }} → {{ $booking->flightSchedule->destination }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">{{ __('departure_date') }}</div>
                            <div class="info-value">{{ optional($booking->flightSchedule->departure_date)->format('d M Y') }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">{{ __('departure_time') }}</div>
                            <div class="info-value">{{ \Illuminate\Support\Str::substr((string) $booking->flightSchedule->departure_time, 0, 5) }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">{{ __('arrival_time') }}</div>
                            <div class="info-value">{{ \Illuminate\Support\Str::substr((string) $booking->flightSchedule->arrival_time, 0, 5) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section">
                <h2 class="section-title">{{ __('payment_details') }}</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">{{ __('total_amount') }}</div>
                        <div class="info-value">Rp{{ number_format($booking->total_price, 0, ',', '.') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">{{ __('payment_status') }}</div>
                        <div class="info-value">{{ __('paid') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">{{ __('payment_method') }}</div>
                        <div class="info-value">{{ $paymentMethodLabel }}</div>
                    </div>
                </div>
            </div>

            <div class="barcode">
                |||||||||||||||||||||||||
                <br>
                {{ $primaryTicket?->qr_code ?? $booking->booking_code }}
                <br>
                |||||||||||||||||||||||||
            </div>
        </div>

        <div class="footer">
            <p>{{ __('e_ticket_footer') }}</p>
            <p>{{ __('generated_on') }} {{ now()->format('d M Y H:i') }}</p>
        </div>
    </div>
</body>
</html>