@extends('layouts.app')

@section('title', __('booking_success'))

@section('content')
    @php
        $passengers = $booking->passengers->values();
        $tickets = $booking->tickets->values();
        $paymentMethodLabel = match ((string) $booking->payment_method) {
            'bank_transfer' => __('payment_bank_transfer'),
            'e_wallet' => __('payment_e_wallet'),
            'credit_card' => __('payment_credit_card'),
            default => '-',
        };
    @endphp
    <div class="mx-auto max-w-4xl space-y-8">
        <header class="space-y-2">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-500/20">
                    <svg class="h-6 w-6 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-emerald-400">{{ __('payment_successful') }}</p>
                    <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">{{ __('booking_confirmed') }}</h1>
                </div>
            </div>
            <p class="text-sm text-slate-400">{{ __('booking_success_desc') }}</p>
        </header>

        <section class="rounded-2xl border border-white/10 bg-slate-900/60 p-6 shadow-xl shadow-black/30 backdrop-blur-sm sm:p-7">
            <div class="space-y-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-sky-300">{{ __('booking_summary') }}</p>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <p class="text-xs uppercase tracking-wider text-slate-500">{{ __('booking_code') }}</p>
                            <p class="mt-1 font-mono text-lg font-bold text-white">{{ $booking->booking_code }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wider text-slate-500">{{ __('status') }}</p>
                            <p class="mt-1 inline-flex items-center gap-2 rounded-full bg-emerald-500/20 px-3 py-1 text-sm font-medium text-emerald-300">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ __('paid') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wider text-slate-500">{{ __('payment_method') }}</p>
                            <p class="mt-1 font-medium text-white">{{ $paymentMethodLabel }}</p>
                        </div>
                    </div>
                </div>

                <div class="border-t border-white/10 pt-6">
                    <p class="text-xs font-semibold uppercase tracking-wider text-sky-300">{{ __('flight_details') }}</p>
                    <div class="mt-4 space-y-4">
                        <div class="flex flex-wrap items-start gap-6">
                            <div>
                                <p class="text-xs uppercase tracking-wider text-slate-500">{{ __('flight') }}</p>
                                <p class="mt-1 font-medium text-white">{{ $booking->flightSchedule->flight_number }}</p>
                                <p class="text-sm text-slate-400">{{ $booking->flightSchedule->airline?->airline_name ?? __('airline_unavailable') }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wider text-slate-500">{{ __('route') }}</p>
                                <p class="mt-1 font-medium text-white">{{ $booking->flightSchedule->origin }} <span class="px-1 text-slate-500">→</span> {{ $booking->flightSchedule->destination }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wider text-slate-500">{{ __('date_time') }}</p>
                                <p class="mt-1 font-medium text-white">{{ optional($booking->flightSchedule->departure_date)->format('d M Y') }} {{ \Illuminate\Support\Str::substr((string) $booking->flightSchedule->departure_time, 0, 5) }}</p>
                            </div>
                        </div>
                        <div class="rounded-xl border border-white/10 bg-slate-950/40 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wider text-sky-300">{{ __('passenger_list') }}</p>
                            <div class="mt-4 space-y-3">
                                @foreach ($passengers as $index => $passenger)
                                    @php
                                        $ticket = $tickets->get($index);
                                    @endphp
                                    <div class="grid gap-3 rounded-lg border border-white/10 bg-slate-900/50 p-3 text-sm sm:grid-cols-5">
                                        <div>
                                            <p class="text-[11px] uppercase tracking-wider text-slate-500">{{ __('passenger') }} #{{ $index + 1 }}</p>
                                            <p class="mt-1 font-medium text-white">{{ $passenger->name }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[11px] uppercase tracking-wider text-slate-500">{{ __('nik') }}</p>
                                            <p class="mt-1 font-medium text-white">{{ $passenger->id_number }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[11px] uppercase tracking-wider text-slate-500">{{ __('class') }}</p>
                                            <p class="mt-1 font-medium text-white">{{ \Illuminate\Support\Str::of((string) $passenger->seat_class)->replace('_', ' ')->title() }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[11px] uppercase tracking-wider text-slate-500">{{ __('ticket_number') }}</p>
                                            <p class="mt-1 font-medium text-white">{{ $ticket?->ticket_number ?? '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[11px] uppercase tracking-wider text-slate-500">{{ __('seat_number') }}</p>
                                            <p class="mt-1 font-medium text-white">{{ $ticket?->seat_number ?? '-' }}</p>
                                        </div>
                                        <div class="sm:col-span-5">
                                            <p class="text-[11px] uppercase tracking-wider text-slate-500">{{ __('qr_code') }}</p>
                                            <p class="mt-1 inline-block rounded bg-slate-950/90 px-2 py-1 font-mono text-xs text-sky-200">{{ $ticket?->qr_code ?? '-' }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-white/10 bg-slate-900/60 p-6 shadow-xl shadow-black/30 backdrop-blur-sm sm:p-7">
            <div class="text-center">
                <h2 class="text-xl font-bold text-white">{{ __('download_e_ticket') }}</h2>
                <p class="mt-2 text-sm text-slate-400">{{ __('e_ticket_desc') }}</p>
                <div class="mt-6">
                    <a
                        href="{{ route('bookings.download-eticket', ['booking' => $booking->id], false) }}"
                        class="inline-flex items-center gap-3 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-sky-500/20 transition hover:from-sky-400 hover:to-indigo-500 hover:shadow-sky-500/35 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-400 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-950"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        {{ __('download_e_ticket') }}
                    </a>
                </div>
            </div>
        </section>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <a
                href="{{ route('flights.search', [], false) }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/10 bg-white/5 px-5 py-3 text-sm font-medium text-slate-200 transition hover:border-sky-500/40 hover:bg-sky-500/10 hover:text-white"
            >
                {{ __('book_another_flight') }}
            </a>
            <a
                href="{{ route('flights.search', [], false) }}"
                class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-sky-500/20 transition hover:from-sky-400 hover:to-indigo-500 hover:shadow-sky-500/35"
            >
                {{ __('back_to_home') }}
            </a>
        </div>
    </div>
@endsection