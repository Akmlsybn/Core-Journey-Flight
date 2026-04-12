@extends('layouts.app')

@section('title', 'Cari penerbangan')

@section('content')
    <div
        id="flight-search-root"
        class="mx-auto max-w-3xl"
        data-calendar-prices-url="{{ route('flights.calendar-prices') }}"
    >
        <div class="mb-10 text-center">
            <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Temukan penerbangan Anda</h1>
            <p class="mt-3 text-slate-400">Masukkan bandara asal, tujuan, dan tanggal keberangkatan.</p>
        </div>

        <div
            class="rounded-2xl border border-white/10 bg-slate-900/60 p-6 shadow-2xl shadow-black/40 backdrop-blur-sm sm:p-8"
        >
            @if ($errors->any())
                <div
                    class="mb-6 rounded-xl border border-rose-500/30 bg-rose-950/40 px-4 py-3 text-sm text-rose-200"
                    role="alert"
                >
                    <p class="font-medium text-rose-100">Periksa kembali input Anda</p>
                    <ul class="mt-2 list-inside list-disc space-y-1 text-rose-200/90">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

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
                            <option value="" disabled {{ old('origin') ? '' : 'selected' }}>Pilih bandara asal</option>
                            @foreach ($airports as $airport)
                                <option value="{{ $airport->airport_code }}" @selected(old('origin', request('origin')) === $airport->airport_code)>
                                    {{ $airport->airport_code }} — {{ $airport->city_name }} ({{ $airport->airport_name }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label for="destination" class="block text-sm font-medium text-slate-200">Ke (kode bandara)</label>
                        <select
                            id="destination"
                            name="destination"
                            required
                            class="block w-full rounded-xl border border-white/10 bg-slate-950/80 px-4 py-3 text-white shadow-inner ring-0 transition focus:border-sky-500/50 focus:outline-none focus:ring-2 focus:ring-sky-500/40"
                        >
                            <option value="" disabled {{ old('destination') ? '' : 'selected' }}>Pilih bandara tujuan</option>
                            @foreach ($airports as $airport)
                                <option value="{{ $airport->airport_code }}" @selected(old('destination', request('destination')) === $airport->airport_code)>
                                    {{ $airport->airport_code }} — {{ $airport->city_name }} ({{ $airport->airport_name }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="departure_date" class="block text-sm font-medium text-slate-200">Tanggal berangkat</label>
                    <p class="text-xs text-slate-500">
                        Kalender memuat harga termurah per hari (async) setelah Anda memilih bandara asal dan tujuan — mirip petunjuk harga pada kalender pemesanan modern.
                    </p>
                    <input
                        type="text"
                        id="departure_date"
                        name="departure_date"
                        value="{{ old('departure_date', request('departure_date')) }}"
                        required
                        autocomplete="off"
                        placeholder="Pilih tanggal"
                        class="block w-full max-w-xs cursor-pointer rounded-xl border border-white/10 bg-slate-950/80 px-4 py-3 text-white shadow-inner placeholder:text-slate-600 focus:border-sky-500/50 focus:outline-none focus:ring-2 focus:ring-sky-500/40"
                    />
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
@endsection
