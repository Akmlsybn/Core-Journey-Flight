@extends('layouts.app')

@section('title', 'Hasil pencarian')

@section('content')
    @php
        $departureLabel = \Illuminate\Support\Carbon::parse($criteria['departure_date'])->format('d/m/Y');
        $passengerCount = (int) ($searchParams['passenger_count'] ?? $criteria['passenger_count'] ?? 1);
        $selectedSeatClass = (string) ($searchParams['seat_class'] ?? $criteria['seat_class'] ?? '');
        $seatClassLabel = str_replace('_', ' ', $selectedSeatClass);
    @endphp

    <div class="space-y-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-medium text-sky-400">Hasil pencarian</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-white sm:text-3xl">
                    {{ $criteria['origin'] }}
                    <span class="mx-2 text-slate-500">→</span>
                    {{ $criteria['destination'] }}
                </h1>
                <p class="mt-2 text-slate-400">{{ $departureLabel }}</p>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center rounded-lg bg-sky-500/15 px-2.5 py-1 text-xs font-medium text-sky-200 ring-1 ring-sky-500/25">
                        Penumpang: {{ $passengerCount }}
                    </span>
                </div>
            </div>
            <a
                href="{{ route('flights.search') }}"
                class="inline-flex items-center justify-center gap-2 self-start rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-medium text-slate-200 transition hover:border-sky-500/40 hover:bg-sky-500/10 hover:text-white"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M19 12H5M12 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                Ubah pencarian
            </a>
        </div>

        @if ($flights->isEmpty())
            <div
                class="rounded-2xl border border-dashed border-white/15 bg-slate-900/40 px-6 py-16 text-center backdrop-blur-sm"
            >
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-800 ring-1 ring-white/10">
                    <svg class="h-7 w-7 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
                        <path d="M3.27 6.96L12 12.01l8.73-5.05M12 22.08V12" />
                    </svg>
                </div>
                <h2 class="mt-6 text-lg font-semibold text-white">Tidak ada penerbangan</h2>
                <p class="mx-auto mt-2 max-w-md text-sm text-slate-400">
                    Untuk rute dan tanggal ini belum ada jadwal di database. Coba tanggal lain sesuai data seed
                    atau jalankan ulang <code class="rounded bg-slate-800 px-1.5 py-0.5 text-xs text-sky-300">php artisan migrate:fresh --seed</code>.
                </p>
            </div>
        @else
            <ul class="space-y-4">
                @foreach ($flights as $flight)
                    @php
                        $statusColors = [
                            'scheduled' => 'bg-emerald-500/15 text-emerald-300 ring-emerald-500/25',
                            'boarding' => 'bg-amber-500/15 text-amber-200 ring-amber-500/25',
                            'departed' => 'bg-sky-500/15 text-sky-200 ring-sky-500/25',
                            'cancelled' => 'bg-rose-500/15 text-rose-200 ring-rose-500/25',
                        ];
                        $statusClass = $statusColors[$flight->flight_status] ?? 'bg-slate-500/15 text-slate-200 ring-slate-500/25';
                        $pricePerPax = (float) ($flight->price ?? 0);
                        $totalPrice = $pricePerPax * max($passengerCount, 1);
                    @endphp
                    <li
                        class="group rounded-2xl border border-white/10 bg-slate-900/50 p-5 shadow-lg shadow-black/20 backdrop-blur-sm transition hover:border-sky-500/25 hover:bg-slate-900/70 sm:p-6"
                    >
                        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                            <div class="min-w-0 flex-1 space-y-4">
                                <div class="flex flex-wrap items-center gap-3">
                                    <span class="text-lg font-bold tracking-tight text-white">{{ $flight->flight_number }}</span>
                                    <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-medium ring-1 {{ $statusClass }}">
                                        {{ ucfirst($flight->flight_status) }}
                                    </span>
                                    @if ($flight->airline)
                                        <span class="text-sm text-slate-400">{{ $flight->airline->airline_name }}</span>
                                    @endif
                                </div>

                                <div class="flex flex-wrap items-baseline gap-6 text-sm">
                                    <div>
                                        <p class="text-xs uppercase tracking-wider text-slate-500">Berangkat</p>
                                        <p class="mt-0.5 font-mono text-lg font-semibold text-white">
                                            {{ \Illuminate\Support\Str::substr($flight->departure_time, 0, 5) }}
                                        </p>
                                        <p class="text-slate-400">{{ $flight->origin }}</p>
                                    </div>
                                    <div class="hidden h-px min-w-[2rem] flex-1 bg-gradient-to-r from-transparent via-white/20 to-transparent sm:block sm:max-w-[6rem]" aria-hidden="true"></div>
                                    <div>
                                        <p class="text-xs uppercase tracking-wider text-slate-500">Tiba</p>
                                        <p class="mt-0.5 font-mono text-lg font-semibold text-white">
                                            {{ \Illuminate\Support\Str::substr($flight->arrival_time, 0, 5) }}
                                        </p>
                                        <p class="text-slate-400">{{ $flight->destination }}</p>
                                    </div>
                                    @if ($flight->route && $flight->route->distance_km)
                                        <div class="w-full sm:w-auto">
                                            <p class="text-xs uppercase tracking-wider text-slate-500">Jarak</p>
                                            <p class="mt-0.5 text-slate-300">{{ number_format($flight->route->distance_km) }} km</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="flex shrink-0 flex-col items-stretch gap-2 border-t border-white/5 pt-4 lg:border-l lg:border-t-0 lg:pl-8 lg:pt-0">
                                <span class="inline-flex items-center self-start rounded-lg bg-indigo-500/15 px-2.5 py-1 text-xs font-medium text-indigo-200 ring-1 ring-indigo-500/25">
                                    Kelas Penerbangan: {{ $seatClassLabel ?: 'N/A' }}
                                </span>
                                <p class="text-xs text-slate-500">Harga per tiket</p>
                                <p class="text-xl font-bold text-white">
                                    Rp{{ number_format($pricePerPax, 0, ',', '.') }} <span class="text-sm font-medium text-slate-400">/ pax</span>
                                </p>
                                <p class="text-xs text-slate-500">Total ({{ $passengerCount }} penumpang)</p>
                                <p class="text-2xl font-bold text-sky-300">Rp{{ number_format($totalPrice, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection