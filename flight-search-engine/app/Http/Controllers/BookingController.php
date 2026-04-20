<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Mail\BookingConfirmedEticketMail;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\FlightSchedule;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService
    ) {
    }

    public function showFlight(Request $request, FlightSchedule $flightSchedule): View
    {
        $this->applyLocale($request);

        $seatClass = (string) $request->query('seat_class', '');
        $passengerCount = max(1, (int) $request->query('passenger_count', '1'));
        $timeFilters = $this->extractTimeFilters($request);
        $backToResultsUrl = $this->resolveInternalBackUrl(
            $request->query('back_to_results'),
            'flights.results',
            array_merge([
                'origin' => (string) $request->query('origin', (string) $flightSchedule->origin),
                'destination' => (string) $request->query('destination', (string) $flightSchedule->destination),
                'departure_date' => (string) $request->query('departure_date', optional($flightSchedule->departure_date)->toDateString()),
                'passenger_count' => $passengerCount,
                'seat_class' => $seatClass,
            ], $timeFilters)
        );

        $flightSchedule->load(['airline', 'seatClasses']);

        $selectedSeatClass = $flightSchedule->seatClasses
            ->firstWhere('seat_class', $seatClass);

        return view('flights.show', [
            'flight' => $flightSchedule,
            'passengerCount' => $passengerCount,
            'seatClass' => $seatClass,
            'selectedSeatPrice' => $selectedSeatClass?->class_price,
            'timeFilters' => $timeFilters,
            'backToResultsUrl' => $backToResultsUrl,
        ]);
    }

    public function create(Request $request, FlightSchedule $flightSchedule): View
    {
        $this->applyLocale($request);

        $seatClass = (string) $request->query('seat_class', '');
        $passengerCount = max(1, (int) $request->query('passenger_count', '1'));
        $timeFilters = $this->extractTimeFilters($request);
        $backToResultsUrl = $this->resolveInternalBackUrl(
            $request->query('back_to_results'),
            'flights.results',
            array_merge([
                'origin' => (string) $request->query('origin', (string) $flightSchedule->origin),
                'destination' => (string) $request->query('destination', (string) $flightSchedule->destination),
                'departure_date' => (string) $request->query('departure_date', optional($flightSchedule->departure_date)->toDateString()),
                'passenger_count' => $passengerCount,
                'seat_class' => $seatClass,
            ], $timeFilters)
        );
        $backToDetailUrl = $this->resolveInternalBackUrl(
            $request->query('back_to_detail'),
            'flights.show',
            array_merge([
                'flightSchedule' => $flightSchedule->id,
                'passenger_count' => $passengerCount,
                'seat_class' => $seatClass,
                'back_to_results' => $backToResultsUrl,
            ], $timeFilters)
        );

        $flightSchedule->load(['airline']);

        $selectedSeatClass = $flightSchedule->seatClasses()
            ->where('seat_class', $seatClass)
            ->first();

        return view('bookings.create', [
            'flight' => $flightSchedule,
            'passengerCount' => $passengerCount,
            'seatClass' => $seatClass,
            'seatPrice' => $selectedSeatClass?->class_price,
            'timeFilters' => $timeFilters,
            'backToDetailUrl' => $backToDetailUrl,
            'backToResultsUrl' => $backToResultsUrl,
            'currentCreateUrl' => $request->fullUrl(),
        ]);
    }

    public function store(StoreBookingRequest $request, FlightSchedule $flightSchedule): RedirectResponse
    {
        $this->applyLocale($request);

        $validated = $request->validated();
        $timeFilters = [
            'departure_slots' => $validated['departure_slots'] ?? [],
            'arrival_slots' => $validated['arrival_slots'] ?? [],
        ];

        try {
            $booking = $this->bookingService->createBooking([
                'flight_schedule_id' => $flightSchedule->id,
                'customer_email' => $validated['booking_email'],
                'ancillary_services' => $validated['ancillary_services'] ?? [],
                'passengers' => $this->buildPassengersPayload($validated),
            ]);
        } catch (\Throwable $exception) {
            return redirect()->back()->withErrors([
                'booking' => $exception->getMessage(),
            ])->withInput();
        }

        return redirect()
            ->route('bookings.payment', [
                'flightSchedule' => $flightSchedule->id,
                'booking_id' => $booking->id,
                'seat_class' => $validated['seat_class'],
                'passenger_count' => $validated['passenger_count'],
                'back_to_detail' => $validated['back_to_detail'] ?? null,
                'back_to_results' => $validated['back_to_results'] ?? null,
                'back_to_form' => $validated['back_to_form'] ?? null,
                'departure_slots' => $timeFilters['departure_slots'],
                'arrival_slots' => $timeFilters['arrival_slots'],
            ])
            ->with('booking_form_success', 'Data penumpang berhasil divalidasi. Silakan lanjut ke tahap pembayaran.')
            ->with('booking_payload', $validated)
            ->with('booking_id', $booking->id);
    }

    public function payment(Request $request, FlightSchedule $flightSchedule): View|RedirectResponse
    {
        $this->applyLocale($request);

        $seatClass = (string) $request->query('seat_class', '');
        $passengerCount = max(1, (int) $request->query('passenger_count', '1'));
        $timeFilters = $this->extractTimeFilters($request);
        $backToResultsUrl = $this->resolveInternalBackUrl($request->query('back_to_results'), 'flights.search');
        $backToDetailUrl = $this->resolveInternalBackUrl(
            $request->query('back_to_detail'),
            'flights.show',
            array_merge([
                'flightSchedule' => $flightSchedule->id,
                'passenger_count' => $passengerCount,
                'seat_class' => $seatClass,
                'back_to_results' => $backToResultsUrl,
            ], $timeFilters)
        );
        $backToFormUrl = $this->resolveInternalBackUrl(
            $request->query('back_to_form'),
            'bookings.create',
            array_merge([
                'flightSchedule' => $flightSchedule->id,
                'passenger_count' => $passengerCount,
                'seat_class' => $seatClass,
                'back_to_detail' => $backToDetailUrl,
                'back_to_results' => $backToResultsUrl,
            ], $timeFilters)
        );

        $bookingIdQuery = $request->query('booking_id');
        $bookingId = is_numeric($bookingIdQuery)
            ? (int) $bookingIdQuery
            : (int) $request->session()->get('booking_id', 0);

        $booking = $bookingId > 0 ? Booking::query()->find($bookingId) : null;

        if (!$booking || (int) $booking->flight_schedule_id !== (int) $flightSchedule->id) {
            return redirect()->to($backToFormUrl)->withErrors([
                'payment' => __('booking_not_found'),
            ]);
        }

        if ($booking->status === 'cancelled') {
            return redirect()->to($backToFormUrl)->withErrors([
                'payment' => __('checkout_expired'),
            ]);
        }

        if ($this->bookingService->cancelExpiredBooking((int) $booking->id)) {
            return redirect()->to($backToFormUrl)->withErrors([
                'payment' => __('checkout_expired'),
            ]);
        }

        return view('bookings.payment', [
            'flight' => $flightSchedule,
            'backToFormUrl' => $backToFormUrl,
            'bookingId' => (int) $booking->id,
            'paymentExpiresAt' => $booking->payment_expires_at?->toIso8601String(),
            'paymentReference' => $this->buildPaymentReference($booking),
            'gatewaySignature' => $this->buildGatewaySignature($booking),
        ]);
    }

    public function confirmPayment(Request $request): View|RedirectResponse
    {
        $this->applyLocale($request);

        $bookingId = $request->input('booking_id');
        $paymentStatus = $request->input('payment_status');
        $paymentMethod = $request->input('payment_method');

        // Validate input
        $request->validate([
            'booking_id' => 'required|integer|exists:bookings,id',
            'payment_status' => 'required|string|in:successful,failed',
            'payment_method' => 'required|string|in:bank_transfer,e_wallet,credit_card',
            'payment_reference' => 'required|string|max:80',
            'gateway_signature' => 'required|string|size:64',
        ]);

        $bookingForVerification = Booking::query()->find((int) $bookingId);
        if (!$bookingForVerification || !$this->verifyGatewaySignature($bookingForVerification, (string) $request->input('payment_reference'), (string) $request->input('gateway_signature'))) {
            return redirect()->back()->withErrors([
                'payment' => __('payment_verification_failed'),
            ])->withInput();
        }

        $success = $this->bookingService->confirmPayment((int) $bookingId, (string) $paymentStatus, (string) $paymentMethod);

        if (!$success) {
            return redirect()->back()->withErrors([
                'payment' => __('payment_failed_try_again'),
            ])->withInput();
        }

        $booking = \App\Models\Booking::with([
            'flightSchedule.airline',
            'passengers' => static fn ($query) => $query->orderBy('id'),
            'tickets' => static fn ($query) => $query->orderBy('id'),
        ])->find($bookingId);

        if (!$booking) {
            return redirect()->route('flights.search')->withErrors([
                'payment' => __('booking_not_found'),
            ]);
        }

        if ($booking->customer_email) {
            try {
                $pdfBinary = app('dompdf.wrapper')->loadView('pdf.eticket', ['booking' => $booking])->output();

                Mail::to($booking->customer_email)->send(
                    new BookingConfirmedEticketMail($booking, $pdfBinary)
                );
            } catch (\Throwable $throwable) {
                Log::warning('Failed sending e-ticket email', [
                    'booking_id' => $booking->id,
                    'booking_code' => $booking->booking_code,
                    'email' => $booking->customer_email,
                    'error' => $throwable->getMessage(),
                ]);
            }
        }

        return view('bookings.success', [
            'booking' => $booking,
        ]);
    }

    public function downloadEticket(Request $request, Booking $booking): \Illuminate\Http\Response
    {
        $this->applyLocale($request);

        // Ensure booking is paid and belongs to user (in real app, add auth check)
        if ($booking->status !== 'paid') {
            abort(403, 'Booking not paid');
        }

        $booking->load([
            'flightSchedule.airline',
            'passengers' => static fn ($query) => $query->orderBy('id'),
            'tickets' => static fn ($query) => $query->orderBy('id'),
        ]);

        $pdf = app('dompdf.wrapper')->loadView('pdf.eticket', [
            'booking' => $booking,
        ]);

        return $pdf->download('e-ticket-' . $booking->booking_code . '.pdf');
    }

    public function switchLanguage(Request $request, string $lang): RedirectResponse
    {
        $normalized = strtolower($lang);
        if (!in_array($normalized, ['id', 'en'], true)) {
            $normalized = 'id';
        }

        $request->session()->put('ui_lang', $normalized);
        App::setLocale($normalized);

        $previousUrl = url()->previous();

        return redirect()->to($previousUrl !== '' ? $previousUrl : route('flights.search'))->withInput();
    }

    private function extractTimeFilters(Request $request): array
    {
        return [
            'departure_slots' => $this->normalizeSlots($request->query('departure_slots', [])),
            'arrival_slots' => $this->normalizeSlots($request->query('arrival_slots', [])),
        ];
    }

    private function normalizeSlots(mixed $slots): array
    {
        if (!is_array($slots)) {
            return [];
        }

        $allowed = ['dawn', 'morning', 'afternoon', 'evening'];

        return array_values(array_intersect($allowed, array_map('strval', $slots)));
    }

    private function applyLocale(Request $request): void
    {
        $locale = strtolower((string) $request->session()->get('ui_lang', 'id'));
        if (!in_array($locale, ['id', 'en'], true)) {
            $locale = 'id';
            $request->session()->put('ui_lang', $locale);
        }

        App::setLocale($locale);
    }

    private function resolveInternalBackUrl(mixed $candidate, string $fallbackRoute, array $fallbackParams = []): string
    {
        $candidateUrl = is_string($candidate) ? trim($candidate) : '';

        if ($candidateUrl !== '' && $this->isInternalUrl($candidateUrl)) {
            return $candidateUrl;
        }

        return route($fallbackRoute, $fallbackParams);
    }

    private function isInternalUrl(string $url): bool
    {
        if (Str::startsWith($url, '/')) {
            return true;
        }

        $appUrl = trim((string) config('app.url'));
        if ($appUrl !== '' && Str::startsWith($url, rtrim($appUrl, '/'))) {
            return true;
        }

        return false;
    }

    private function buildPassengersPayload(array $validated): array
    {
        $passengerCount = max(1, (int) ($validated['passenger_count'] ?? 1));
        $passengerNames = $validated['passenger_names'] ?? [];
        $passengerNiks = $validated['passenger_niks'] ?? [];
        $passengerDobs = $validated['passenger_dobs'] ?? [];
        $passengerPhones = $validated['passenger_phones'] ?? [];
        $seatClass = (string) ($validated['seat_class'] ?? 'economy');

        $passengers = [];
        for ($i = 1; $i <= $passengerCount; $i++) {
            $fallbackName = __('passenger') . ' ' . $i;
            $passengers[] = [
                'name' => trim((string) ($passengerNames[$i - 1] ?? $fallbackName)),
                'id_number' => trim((string) ($passengerNiks[$i - 1] ?? '')),
                'date_of_birth' => (string) ($passengerDobs[$i - 1] ?? ''),
                'phone' => trim((string) ($passengerPhones[$i - 1] ?? '')),
                'seat_class' => $seatClass,
            ];
        }

        return $passengers;
    }

    private function buildPaymentReference(Booking $booking): string
    {
        return 'PAY-' . $booking->booking_code;
    }

    private function buildGatewaySignature(Booking $booking): string
    {
        $payload = implode('|', [
            $booking->id,
            $booking->booking_code,
            $this->buildPaymentReference($booking),
        ]);

        return hash_hmac('sha256', $payload, (string) config('app.key'));
    }

    private function verifyGatewaySignature(Booking $booking, string $paymentReference, string $signature): bool
    {
        if ($paymentReference !== $this->buildPaymentReference($booking)) {
            return false;
        }

        return hash_equals($this->buildGatewaySignature($booking), $signature);
    }
}
