@extends('layouts.app')

@section('title', __('passenger_form_title'))

@section('content')
    @php
        $departureDate = optional($flight->departure_date)->format('d M Y') ?? '-';
        $departureTime = \Illuminate\Support\Str::substr((string) $flight->departure_time, 0, 5);
        $arrivalTime = \Illuminate\Support\Str::substr((string) $flight->arrival_time, 0, 5);
        $seatClassLabel = \Illuminate\Support\Str::of((string) $seatClass)->replace('_', ' ')->title();
        $totalEstimatedPrice = (float) ($seatPrice ?? 0) * max((int) $passengerCount, 1);
        $timeFilters = $timeFilters ?? ['departure_slots' => [], 'arrival_slots' => []];
        $selectedAncillaryServices = old('ancillary_services', []);
        $bookingEmail = old('booking_email', '');
        $passengerNames = old('passenger_names', array_fill(0, max((int) $passengerCount, 1), ''));
        $passengerNiks = old('passenger_niks', array_fill(0, max((int) $passengerCount, 1), ''));
        $passengerDobs = old('passenger_dobs', array_fill(0, max((int) $passengerCount, 1), ''));
        $passengerPhones = old('passenger_phones', array_fill(0, max((int) $passengerCount, 1), ''));
    @endphp

    <div class="mx-auto max-w-4xl space-y-8">
        <header class="space-y-2">
            <p class="text-sm font-medium text-sky-400">{{ __('booking_label') }}</p>
            <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">{{ __('complete_passenger_data') }}</h1>
            <p class="text-sm text-slate-400">{{ __('passenger_form_subtitle') }}</p>
        </header>

        <section class="rounded-2xl border border-white/10 bg-slate-900/60 p-6 shadow-xl shadow-black/30 backdrop-blur-sm sm:p-7">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div class="space-y-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-sky-300">{{ __('flight_summary') }}</p>
                    <div>
                        <p class="text-xl font-bold text-white">{{ $flight->flight_number }}</p>
                        <p class="text-sm text-slate-400">{{ $flight->airline?->airline_name ?? __('airline_unavailable') }}</p>
                    </div>
                    <div class="flex flex-wrap items-start gap-6 text-sm">
                        <div>
                            <p class="text-xs uppercase tracking-wider text-slate-500">{{ __('route') }}</p>
                            <p class="mt-1 font-medium text-slate-200">{{ $flight->origin }} <span class="px-1 text-slate-500">→</span> {{ $flight->destination }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wider text-slate-500">{{ __('date') }}</p>
                            <p class="mt-1 font-medium text-slate-200">{{ $departureDate }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wider text-slate-500">{{ __('time') }}</p>
                            <p class="mt-1 font-medium text-slate-200">{{ $departureTime }} - {{ $arrivalTime }}</p>
                        </div>
                    </div>
                </div>

                <div class="w-full max-w-xs rounded-xl border border-white/10 bg-slate-950/60 p-4">
                    <p class="text-xs uppercase tracking-wider text-slate-500">{{ __('fare_detail') }}</p>
                    <div class="mt-3 space-y-2 text-sm">
                        <p class="flex items-center justify-between text-slate-300"><span>{{ __('class') }}</span><span class="font-medium text-white">{{ $seatClassLabel ?: '-' }}</span></p>
                        <p class="flex items-center justify-between text-slate-300"><span>{{ __('label_passengers') }}</span><span class="font-medium text-white">{{ $passengerCount }}</span></p>
                        <p class="flex items-center justify-between text-slate-300"><span>{{ __('price_per_ticket') }}</span><span class="font-medium text-white">Rp{{ number_format((float) ($seatPrice ?? 0), 0, ',', '.') }}</span></p>
                        <p class="border-t border-white/10 pt-2 text-base font-semibold text-sky-300">{{ __('estimated_total') }}: Rp{{ number_format($totalEstimatedPrice, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-white/10 bg-slate-900/60 p-6 shadow-xl shadow-black/30 backdrop-blur-sm sm:p-7">
            @if (session('booking_form_success'))
                <div class="mb-5 rounded-xl border border-emerald-400/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                    {{ session('booking_form_success') }}
                </div>
            @endif
            @error('booking')
                <div class="mb-5 rounded-xl border border-rose-400/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                    {{ $message }}
                </div>
            @enderror

            <form method="POST" action="{{ route('bookings.store', ['flightSchedule' => $flight->id], false) }}" class="space-y-5">
                @csrf
                <input type="hidden" name="seat_class" value="{{ old('seat_class', $seatClass) }}">
                <input type="hidden" name="passenger_count" value="{{ old('passenger_count', $passengerCount) }}">
                <input type="hidden" name="back_to_detail" value="{{ old('back_to_detail', $backToDetailUrl) }}">
                <input type="hidden" name="back_to_results" value="{{ old('back_to_results', $backToResultsUrl) }}">
                <input type="hidden" name="back_to_form" value="{{ old('back_to_form', $currentCreateUrl) }}">
                @foreach ($timeFilters['departure_slots'] as $slot)
                    <input type="hidden" name="departure_slots[]" value="{{ $slot }}">
                @endforeach
                @foreach ($timeFilters['arrival_slots'] as $slot)
                    <input type="hidden" name="arrival_slots[]" value="{{ $slot }}">
                @endforeach

                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="space-y-2 sm:col-span-2">
                        <label for="booking_email" class="block text-sm font-medium text-slate-200">{{ __('booking_email') }}</label>
                        <input
                            type="email"
                            id="booking_email"
                            name="booking_email"
                            value="{{ $bookingEmail }}"
                            placeholder="{{ __('booking_email_placeholder') }}"
                            autocomplete="email"
                            class="block w-full rounded-xl border border-white/10 bg-slate-950/80 px-4 py-3 text-white shadow-inner placeholder:text-slate-500 focus:border-sky-500/50 focus:outline-none focus:ring-2 focus:ring-sky-500/40 @error('booking_email') border-rose-400/60 focus:border-rose-400/70 focus:ring-rose-400/40 @enderror"
                        >
                        @error('booking_email')
                            <p class="text-sm text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2 sm:col-span-2">
                        <p class="block text-sm font-medium text-slate-200">{{ __('passenger_names_heading') }}</p>
                        <p class="text-xs text-slate-400">{{ __('passenger_names_hint') }}</p>
                    </div>

                    @for ($i = 0; $i < max((int) $passengerCount, 1); $i++)
                        <div class="space-y-2 sm:col-span-2">
                            <label for="passenger_names_{{ $i }}" class="block text-sm font-medium text-slate-200">{{ __('passenger_name_index', ['number' => $i + 1]) }}</label>
                            <input
                                type="text"
                                id="passenger_names_{{ $i }}"
                                name="passenger_names[]"
                                value="{{ $passengerNames[$i] ?? '' }}"
                                placeholder="{{ __('passenger_name_placeholder') }}"
                                autocomplete="name"
                                class="block w-full rounded-xl border border-white/10 bg-slate-950/80 px-4 py-3 text-white shadow-inner placeholder:text-slate-500 focus:border-sky-500/50 focus:outline-none focus:ring-2 focus:ring-sky-500/40 @error('passenger_names.' . $i) border-rose-400/60 focus:border-rose-400/70 focus:ring-rose-400/40 @enderror"
                            >
                            @error('passenger_names.' . $i)
                                <p class="text-sm text-rose-300">{{ $message }}</p>
                            @enderror

                            <label for="passenger_niks_{{ $i }}" class="mt-3 block text-sm font-medium text-slate-200">{{ __('passenger_nik_index', ['number' => $i + 1]) }}</label>
                            <input
                                type="text"
                                id="passenger_niks_{{ $i }}"
                                name="passenger_niks[]"
                                value="{{ $passengerNiks[$i] ?? '' }}"
                                placeholder="{{ __('passenger_nik_placeholder') }}"
                                inputmode="numeric"
                                minlength="16"
                                maxlength="16"
                                autocomplete="off"
                                class="block w-full rounded-xl border border-white/10 bg-slate-950/80 px-4 py-3 text-white shadow-inner placeholder:text-slate-500 focus:border-sky-500/50 focus:outline-none focus:ring-2 focus:ring-sky-500/40 @error('passenger_niks.' . $i) border-rose-400/60 focus:border-rose-400/70 focus:ring-rose-400/40 @enderror"
                            >
                            @error('passenger_niks.' . $i)
                                <p class="text-sm text-rose-300">{{ $message }}</p>
                            @enderror

                            <label for="passenger_dobs_{{ $i }}" class="mt-3 block text-sm font-medium text-slate-200">{{ __('passenger_dob_index', ['number' => $i + 1]) }}</label>
                            <input
                                type="date"
                                id="passenger_dobs_{{ $i }}"
                                name="passenger_dobs[]"
                                value="{{ $passengerDobs[$i] ?? '' }}"
                                class="block w-full rounded-xl border border-white/10 bg-slate-950/80 px-4 py-3 text-white shadow-inner focus:border-sky-500/50 focus:outline-none focus:ring-2 focus:ring-sky-500/40 @error('passenger_dobs.' . $i) border-rose-400/60 focus:border-rose-400/70 focus:ring-rose-400/40 @enderror"
                            >
                            @error('passenger_dobs.' . $i)
                                <p class="text-sm text-rose-300">{{ $message }}</p>
                            @enderror

                            <label for="passenger_phones_{{ $i }}" class="mt-3 block text-sm font-medium text-slate-200">{{ __('passenger_phone_index', ['number' => $i + 1]) }}</label>
                            <input
                                type="text"
                                id="passenger_phones_{{ $i }}"
                                name="passenger_phones[]"
                                value="{{ $passengerPhones[$i] ?? '' }}"
                                placeholder="{{ __('passenger_phone_placeholder') }}"
                                inputmode="tel"
                                autocomplete="tel"
                                class="block w-full rounded-xl border border-white/10 bg-slate-950/80 px-4 py-3 text-white shadow-inner placeholder:text-slate-500 focus:border-sky-500/50 focus:outline-none focus:ring-2 focus:ring-sky-500/40 @error('passenger_phones.' . $i) border-rose-400/60 focus:border-rose-400/70 focus:ring-rose-400/40 @enderror"
                            >
                            @error('passenger_phones.' . $i)
                                <p class="text-sm text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>
                    @endfor

                    <div class="space-y-2 sm:col-span-2">
                        @error('passenger_names')
                            <p class="text-sm text-rose-300">{{ $message }}</p>
                        @enderror
                        @error('passenger_niks')
                            <p class="text-sm text-rose-300">{{ $message }}</p>
                        @enderror
                        @error('passenger_dobs')
                            <p class="text-sm text-rose-300">{{ $message }}</p>
                        @enderror
                        @error('passenger_phones')
                            <p class="text-sm text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="rounded-xl border border-white/10 bg-slate-950/40 p-4">
                    <p class="text-sm font-medium text-white">{{ __('ancillary_services') }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ __('ancillary_services_desc') }}</p>
                    <div class="mt-3 space-y-2">
                        <label class="inline-flex items-center gap-2 text-sm text-slate-200">
                            <input
                                type="checkbox"
                                name="ancillary_services[]"
                                value="travel_insurance"
                                @checked(in_array('travel_insurance', $selectedAncillaryServices, true))
                                class="h-4 w-4 rounded border-white/20 bg-slate-900 text-sky-500 focus:ring-sky-500/50"
                            >
                            {{ __('travel_insurance') }}
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm text-slate-200">
                            <input
                                type="checkbox"
                                name="ancillary_services[]"
                                value="extra_baggage"
                                @checked(in_array('extra_baggage', $selectedAncillaryServices, true))
                                class="h-4 w-4 rounded border-white/20 bg-slate-900 text-sky-500 focus:ring-sky-500/50"
                            >
                            {{ __('extra_baggage') }}
                        </label>
                    </div>
                    @error('ancillary_services')
                        <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                    @enderror
                    @error('ancillary_services.*')
                        <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                    @enderror
                </div>

                @error('seat_class')
                    <p class="text-sm text-rose-300">{{ $message }}</p>
                @enderror

                @error('passenger_count')
                    <p class="text-sm text-rose-300">{{ $message }}</p>
                @enderror

                <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:items-center sm:justify-between">
                    <a
                        href="{{ $backToDetailUrl }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/10 bg-white/5 px-5 py-3 text-sm font-medium text-slate-200 transition hover:border-sky-500/40 hover:bg-sky-500/10 hover:text-white"
                    >
                        {{ __('back_to_flight_detail') }}
                    </a>
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-sky-500/20 transition hover:from-sky-400 hover:to-indigo-500 hover:shadow-sky-500/35 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-400 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-950"
                    >
                        {{ __('continue_to_payment') }}
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection
