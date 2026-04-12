@extends('layouts.app')

@section('title', 'Cari penerbangan')

@section('content')
    <div
        id="flight-search-root"
        class="mx-auto max-w-3xl"
        data-available-dates='@json($availableDepartureDates ?? [])'
        data-available-dates-url="{{ route('flights.available-dates') }}"
    >
        <div class="mb-10 text-center">
            <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Temukan penerbangan Anda</h1>
            <p class="mt-3 text-slate-400">Masukkan bandara asal, tujuan, tanggal keberangkatan, kelas, dan jumlah penumpang.</p>
        </div>

        <div class="rounded-2xl border border-white/10 bg-slate-900/60 p-6 shadow-2xl shadow-black/40 backdrop-blur-sm sm:p-8">
            <form id="flight-search-form" method="get" action="{{ route('flights.results') }}" class="space-y-6">
                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="space-y-2">
                        <label for="origin" class="block text-sm font-medium text-slate-200">Dari (kode bandara)</label>
                        <select
                            id="origin"
                            name="origin"
                            required
                            class="block w-full rounded-xl border border-white/10 bg-slate-950/80 px-4 py-3 text-white shadow-inner ring-0 transition focus:border-sky-500/50 focus:outline-none focus:ring-2 focus:ring-sky-500/40"
                        >
                            <option value="" disabled @selected(old('origin', request('origin')) === null || old('origin', request('origin')) === '')>Pilih bandara asal</option>
                            @foreach ($airports as $airport)
                                <option value="{{ $airport->airport_code }}" @selected(old('origin', request('origin')) === $airport->airport_code)>
                                    {{ $airport->airport_code }} — {{ $airport->city_name }} ({{ $airport->airport_name }})
                                </option>
                            @endforeach
                        </select>
                        @error('origin')
                            <p class="text-sm text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="destination" class="block text-sm font-medium text-slate-200">Ke (kode bandara)</label>
                        <select
                            id="destination"
                            name="destination"
                            required
                            class="block w-full rounded-xl border border-white/10 bg-slate-950/80 px-4 py-3 text-white shadow-inner ring-0 transition focus:border-sky-500/50 focus:outline-none focus:ring-2 focus:ring-sky-500/40"
                        >
                            <option value="" disabled @selected(old('destination', request('destination')) === null || old('destination', request('destination')) === '')>Pilih bandara tujuan</option>
                            @foreach ($airports as $airport)
                                <option value="{{ $airport->airport_code }}" @selected(old('destination', request('destination')) === $airport->airport_code)>
                                    {{ $airport->airport_code }} — {{ $airport->city_name }} ({{ $airport->airport_name }})
                                </option>
                            @endforeach
                        </select>
                        @error('destination')
                            <p class="text-sm text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="departure_date" class="block text-sm font-medium text-slate-200">Tanggal berangkat</label>
                    <input
                        type="date"
                        id="departure_date"
                        name="departure_date"
                        value="{{ old('departure_date', request('departure_date')) }}"
                        min="{{ today()->toDateString() }}"
                        required
                        class="block w-full max-w-xs cursor-pointer rounded-xl border border-white/10 bg-slate-950/80 px-4 py-3 text-white shadow-inner placeholder:text-slate-600 focus:border-sky-500/50 focus:outline-none focus:ring-2 focus:ring-sky-500/40"
                    />
                    @error('departure_date')
                        <p class="text-sm text-rose-300">{{ $message }}</p>
                    @enderror
                    <p id="departure-date-help" class="text-xs text-slate-500">Pilih tanggal yang tersedia pada jadwal penerbangan.</p>
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="space-y-2">
                        <label for="passenger_count" class="block text-sm font-medium text-slate-200">Jumlah penumpang</label>
                        <select
                            id="passenger_count"
                            name="passenger_count"
                            required
                            class="block w-full rounded-xl border border-white/10 bg-slate-950/80 px-4 py-3 text-white shadow-inner ring-0 transition focus:border-sky-500/50 focus:outline-none focus:ring-2 focus:ring-sky-500/40"
                        >
                            <option value="" disabled @selected(old('passenger_count', request('passenger_count')) === null || old('passenger_count', request('passenger_count')) === '')>Pilih jumlah</option>
                            @for ($i = 1; $i <= 7; $i++)
                                <option value="{{ $i }}" @selected((string) old('passenger_count', request('passenger_count')) === (string) $i)>
                                    {{ $i }} penumpang
                                </option>
                            @endfor
                        </select>
                        @error('passenger_count')
                            <p class="text-sm text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="seat_class" class="block text-sm font-medium text-slate-200">Kelas penerbangan</label>
                        <select
                            id="seat_class"
                            name="seat_class"
                            required
                            class="block w-full rounded-xl border border-white/10 bg-slate-950/80 px-4 py-3 text-white shadow-inner ring-0 transition focus:border-sky-500/50 focus:outline-none focus:ring-2 focus:ring-sky-500/40"
                        >
                            <option value="" disabled @selected(old('seat_class', request('seat_class')) === null || old('seat_class', request('seat_class')) === '')>Pilih kelas</option>
                            <option value="economy" @selected(old('seat_class', request('seat_class')) === 'economy')>Economy</option>
                            <option value="business" @selected(old('seat_class', request('seat_class')) === 'business')>Business</option>
                            <option value="first_class" @selected(old('seat_class', request('seat_class')) === 'first_class')>First Class</option>
                        </select>
                        @error('seat_class')
                            <p class="text-sm text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs text-slate-500">Data diambil dari basis data lokal (seed demo).</p>
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-sky-500/25 transition hover:from-sky-400 hover:to-indigo-500 hover:shadow-sky-500/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-400 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-950"
                    >
                        <span>Cari penerbangan</span>
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M5 12h14M12 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const root = document.getElementById('flight-search-root');
            const form = document.getElementById('flight-search-form');
            const dateInput = document.getElementById('departure_date');
            const help = document.getElementById('departure-date-help');
            const originInput = document.getElementById('origin');
            const destinationInput = document.getElementById('destination');
            const availableDatesUrl = root.dataset.availableDatesUrl;

            if (!root || !form || !dateInput || !originInput || !destinationInput) return;

            let availableDates = JSON.parse(root.dataset.availableDates || '[]');
            let availableDateSet = new Set(availableDates);
            let flatpickrInstance = null;

            function initOrUpdateFlatpickr() {
                if (!window.flatpickr) {
                    return;
                }

                if (flatpickrInstance) {
                    flatpickrInstance.destroy();
                    flatpickrInstance = null;
                }

                flatpickrInstance = window.flatpickr(dateInput, {
                    dateFormat: 'Y-m-d',
                    minDate: 'today',
                    enable: availableDates.length ? availableDates : undefined,
                });
            }

            function updateHelpText() {
                if (!help) {
                    return;
                }

                if (availableDates.length > 0) {
                    help.textContent = 'Tanggal tersedia: ' + availableDates.join(', ');
                    return;
                }

                help.textContent = 'Belum ada jadwal untuk rute yang dipilih.';
            }

            async function fetchAvailableDatesByRoute() {
                if (!availableDatesUrl) {
                    return;
                }

                const origin = originInput.value || '';
                const destination = destinationInput.value || '';

                const query = new URLSearchParams({
                    origin,
                    destination,
                });

                try {
                    const response = await fetch(`${availableDatesUrl}?${query.toString()}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!response.ok) {
                        return;
                    }

                    const payload = await response.json();
                    const nextDates = Array.isArray(payload.data) ? payload.data : [];

                    availableDates = nextDates;
                    availableDateSet = new Set(nextDates);

                    if (dateInput.value && !availableDateSet.has(dateInput.value)) {
                        dateInput.value = '';
                    }

                    updateHelpText();
                    initOrUpdateFlatpickr();
                    validateDepartureDate();
                } catch (error) {
                    // no-op
                }
            }

            function validateDepartureDate() {
                const selected = dateInput.value;

                if (!selected) {
                    dateInput.setCustomValidity('');
                    return;
                }

                if (!availableDateSet.has(selected)) {
                    dateInput.setCustomValidity('Tanggal ini belum memiliki jadwal penerbangan.');
                } else {
                    dateInput.setCustomValidity('');
                }
            }

            updateHelpText();
            initOrUpdateFlatpickr();

            dateInput.addEventListener('input', validateDepartureDate);
            dateInput.addEventListener('change', validateDepartureDate);
            originInput.addEventListener('change', fetchAvailableDatesByRoute);
            destinationInput.addEventListener('change', fetchAvailableDatesByRoute);

            form.addEventListener('submit', function (event) {
                validateDepartureDate();
                if (!dateInput.checkValidity()) {
                    event.preventDefault();
                    dateInput.reportValidity();
                }
            });
        })();
    </script>
@endsection