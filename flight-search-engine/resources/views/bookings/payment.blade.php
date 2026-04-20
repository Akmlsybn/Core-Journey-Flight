@extends('layouts.app')

@section('title', __('payment_method'))

@section('content')
    <div class="mx-auto max-w-3xl">
        <section class="rounded-2xl border border-white/10 bg-slate-900/60 p-6 shadow-xl shadow-black/30 backdrop-blur-sm sm:p-8">
            <p class="text-sm font-medium text-sky-400">{{ __('next_step') }}</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-white sm:text-3xl">{{ __('payment_method_page') }}</h1>
            <p class="mt-3 text-sm text-slate-300">
                {{ __('payment_placeholder_desc') }}
            </p>
            @error('payment')
                <div class="mt-5 rounded-xl border border-rose-400/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                    {{ $message }}
                </div>
            @enderror
            @if (session('booking_form_success'))
                <div class="mt-5 rounded-xl border border-emerald-400/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                    {{ session('booking_form_success') }}
                </div>
            @endif

            <div class="mt-5 rounded-xl border border-amber-400/30 bg-amber-500/10 px-4 py-3 text-sm text-amber-100">
                <p class="font-semibold">{{ __('checkout_timer_title') }}</p>
                <p class="mt-1">{{ __('checkout_timer_desc') }}</p>
                <p id="checkout-countdown" class="mt-2 text-xl font-bold text-amber-300" data-expire-at="{{ $paymentExpiresAt ?? '' }}">--:--</p>
            </div>

            <div class="mt-6">
                <form method="POST" action="{{ route('bookings.confirm-payment', [], false) }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="booking_id" value="{{ old('booking_id', $bookingId ?? session('booking_id')) }}">
                    <input type="hidden" name="payment_status" value="successful">
                    <input type="hidden" name="payment_reference" value="{{ old('payment_reference', $paymentReference ?? '') }}">
                    <input type="hidden" name="gateway_signature" value="{{ old('gateway_signature', $gatewaySignature ?? '') }}">

                    <div class="rounded-xl border border-white/10 bg-slate-950/60 p-4">
                        <p class="text-sm font-medium text-white">{{ __('payment_method') }}</p>
                        <p class="mt-1 text-xs text-slate-400">{{ __('payment_method_options_desc') }}</p>
                        <div class="mt-3 space-y-2">
                            <div class="flex items-center gap-2">
                                <input type="radio" id="bank_transfer" name="payment_method" value="bank_transfer" @checked(old('payment_method', 'bank_transfer') === 'bank_transfer') class="h-4 w-4 text-sky-500">
                                <label for="bank_transfer" class="text-sm text-slate-200">{{ __('payment_bank_transfer') }}</label>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="radio" id="e_wallet" name="payment_method" value="e_wallet" @checked(old('payment_method') === 'e_wallet') class="h-4 w-4 text-sky-500">
                                <label for="e_wallet" class="text-sm text-slate-200">{{ __('payment_e_wallet') }}</label>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="radio" id="credit_card" name="payment_method" value="credit_card" @checked(old('payment_method') === 'credit_card') class="h-4 w-4 text-sky-500">
                                <label for="credit_card" class="text-sm text-slate-200">{{ __('payment_credit_card') }}</label>
                            </div>
                        </div>
                        @error('payment_method')
                            <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <a
                            href="{{ $backToFormUrl }}"
                            class="inline-flex items-center justify-center rounded-xl border border-white/10 bg-white/5 px-5 py-2.5 text-sm font-medium text-slate-200 transition hover:border-sky-500/40 hover:bg-sky-500/10 hover:text-white"
                        >
                            {{ __('back_to_fill_data') }}
                        </a>
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-sky-500/20 transition hover:from-sky-400 hover:to-indigo-500 hover:shadow-sky-500/35"
                        >
                            {{ __('confirm_payment') }}
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <script>
        (function () {
            var countdownEl = document.getElementById('checkout-countdown');
            if (!countdownEl) {
                return;
            }

            var expireAtRaw = countdownEl.getAttribute('data-expire-at');
            if (!expireAtRaw) {
                countdownEl.textContent = '--:--';
                return;
            }

            var expireAt = new Date(expireAtRaw).getTime();
            if (Number.isNaN(expireAt)) {
                countdownEl.textContent = '--:--';
                return;
            }

            var updateCountdown = function () {
                var now = Date.now();
                var remainingMs = expireAt - now;

                if (remainingMs <= 0) {
                    countdownEl.textContent = '00:00';
                    window.location.reload();
                    return;
                }

                var totalSeconds = Math.floor(remainingMs / 1000);
                var minutes = Math.floor(totalSeconds / 60);
                var seconds = totalSeconds % 60;

                countdownEl.textContent = String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
            };

            updateCountdown();
            setInterval(updateCountdown, 1000);
        }());
    </script>
@endsection
