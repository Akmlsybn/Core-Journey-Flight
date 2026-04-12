import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';

function formatShortIdr(value) {
    const n = Number(value);
    if (!Number.isFinite(n)) return '';
    if (n >= 1_000_000) {
        const jt = n / 1_000_000;
        const s = jt >= 10 ? jt.toFixed(0) : jt.toFixed(1).replace(/\.0$/, '');
        return `Rp${s}jt`;
    }
    if (n >= 1_000) {
        return `Rp${Math.round(n / 1_000)}rb`;
    }
    return `Rp${n}`;
}

function buildCalendarUrl(base, origin, destination, year, month) {
    const url = new URL(base, window.location.origin);
    url.searchParams.set('origin', origin);
    url.searchParams.set('destination', destination);
    url.searchParams.set('year', String(year));
    url.searchParams.set('month', String(month));
    return url.toString();
}

export function initFlightSearchCalendar() {
    const root = document.getElementById('flight-search-root');
    const originEl = document.getElementById('origin');
    const destEl = document.getElementById('destination');
    const dateInput = document.getElementById('departure_date');

    if (!root || !originEl || !destEl || !dateInput) {
        return;
    }

    const calendarPricesUrl = root.dataset.calendarPricesUrl;
    if (!calendarPricesUrl) {
        return;
    }

    /** @type {Record<string, number>} */
    let priceByDate = {};
    let fetchSeq = 0;
    let abortController = null;

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    async function loadPricesForMonth(year, month) {
        const origin = originEl.value;
        const destination = destEl.value;

        if (!origin || !destination || origin === destination) {
            priceByDate = {};
            return;
        }

        const seq = ++fetchSeq;
        abortController?.abort();
        abortController = new AbortController();

        const url = buildCalendarUrl(calendarPricesUrl, origin, destination, year, month);

        try {
            const res = await fetch(url, {
                signal: abortController.signal,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!res.ok) {
                if (seq === fetchSeq) {
                    priceByDate = {};
                }
                return;
            }

            const data = await res.json();
            if (seq !== fetchSeq) {
                return;
            }

            const next = {};
            for (const row of data.days ?? []) {
                if (row.date != null && row.min_price != null) {
                    next[row.date] = Number(row.min_price);
                }
            }
            priceByDate = next;
        } catch (e) {
            if (e.name === 'AbortError') {
                return;
            }
            if (seq === fetchSeq) {
                priceByDate = {};
            }
        }
    }

    const fp = flatpickr(dateInput, {
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd/m/Y',
        altInputClass:
            'flatpickr-alt block w-full max-w-xs cursor-pointer rounded-xl border border-white/10 bg-slate-950/80 px-4 py-3 text-white shadow-inner placeholder:text-slate-600 focus:border-sky-500/50 focus:outline-none focus:ring-2 focus:ring-sky-500/40',
        minDate: today,
        disableMobile: true,
        locale: {
            firstDayOfWeek: 1,
        },
        onReady: async (selectedDates, dateStr, instance) => {
            await loadPricesForMonth(instance.currentYear, instance.currentMonth + 1);
            instance.redraw();
        },
        onOpen: async (selectedDates, dateStr, instance) => {
            await loadPricesForMonth(instance.currentYear, instance.currentMonth + 1);
            instance.redraw();
        },
        onMonthChange: async (selectedDates, dateStr, instance) => {
            await loadPricesForMonth(instance.currentYear, instance.currentMonth + 1);
            instance.redraw();
        },
        onYearChange: async (selectedDates, dateStr, instance) => {
            await loadPricesForMonth(instance.currentYear, instance.currentMonth + 1);
            instance.redraw();
        },
        onDayCreate: (selectedDates, dateStr, instance, dayElem) => {
            dayElem.querySelectorAll('.flight-calendar__day-price').forEach((el) => el.remove());
            const dObj = dayElem.dateObj;
            if (!dObj) {
                return;
            }

            const key = instance.formatDate(dObj, 'Y-m-d');
            const price = priceByDate[key];
            if (price == null) {
                return;
            }

            const label = document.createElement('span');
            label.className = 'flight-calendar__day-price';
            label.textContent = formatShortIdr(price);
            label.setAttribute('aria-label', `Mulai dari ${formatShortIdr(price)}`);
            dayElem.appendChild(label);
        },
    });

    const refreshFromRoute = async () => {
        priceByDate = {};
        await loadPricesForMonth(fp.currentYear, fp.currentMonth + 1);
        fp.redraw();
    };

    originEl.addEventListener('change', refreshFromRoute);
    destEl.addEventListener('change', refreshFromRoute);
}
