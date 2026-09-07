// Real-time availability checker (room & hall). Progressive enhancement:
// gagal jaringan / input belum lengkap -> form tetap bisa disubmit (validasi server).
(() => {
    const debounce = (fn, ms) => {
        let t;
        return (...args) => {
            clearTimeout(t);
            t = setTimeout(() => fn(...args), ms);
        };
    };

    const setStatus = (form, { state, message, ctaUrl, ctaLabel }) => {
        const box = form.querySelector('[data-availability-status]');
        const msg = form.querySelector('[data-availability-message]');
        const submit = form.querySelector('[data-availability-submit]');
        if (!box || !msg) return;

        if (!state || !message) {
            box.classList.add('hidden');
            if (submit) submit.disabled = form.hasAttribute('data-sold-out');
            return;
        }

        box.classList.remove('hidden');
        msg.textContent = message;

        box.classList.remove('border-successful/20', 'bg-successful/5', 'text-successful', 'border-danger/20', 'bg-danger/5', 'text-danger', 'border-line/70', 'bg-surface-muted', 'text-stone');
        if (state === 'ok') {
            box.classList.add('border-successful/20', 'bg-successful/5', 'text-successful');
        } else if (state === 'loading') {
            box.classList.add('border-line/70', 'bg-surface-muted', 'text-stone');
        } else {
            box.classList.add('border-danger/20', 'bg-danger/5', 'text-danger');
        }

        let link = box.querySelector('[data-availability-cta]');
        if (ctaUrl && !link) {
            link = document.createElement('a');
            link.setAttribute('data-availability-cta', '');
            link.className = 'mt-1 inline-block text-xs font-semibold underline underline-offset-4';
            box.appendChild(link);
        }
        if (link) {
            if (ctaUrl) {
                link.href = ctaUrl;
                link.textContent = ctaLabel || 'Lihat kamar lain';
                link.classList.remove('hidden');
            } else {
                link.classList.add('hidden');
            }
        }

        if (submit) {
            submit.disabled = state !== 'ok' || form.hasAttribute('data-sold-out');
        }
    };

    const checkRoom = async (form, signal) => {
        const id = form.querySelector('[name="room_type_id"]')?.value;
        const ci = form.querySelector('[name="check_in_date"]')?.value;
        const co = form.querySelector('[name="check_out_date"]')?.value;
        const n = form.querySelector('[name="number_of_rooms"]')?.value || '1';
        if (!id || !ci || !co) {
            setStatus(form, {});
            return;
        }
        const url = `/api/availability/room?room_type_id=${encodeURIComponent(id)}&check_in_date=${encodeURIComponent(ci)}&check_out_date=${encodeURIComponent(co)}&number_of_rooms=${encodeURIComponent(n)}`;
        const res = await fetch(url, { signal, headers: { Accept: 'application/json' } });
        const data = await res.json();
        if (!res.ok) {
            setStatus(form, res.status === 422
                ? { state: 'full', message: data.message || 'Tanggal belum valid.', ctaUrl: form.dataset.ctaUrl, ctaLabel: form.dataset.ctaLabel }
                : {});
            return;
        }
        setStatus(form, {
            state: data.available ? 'ok' : 'full',
            message: data.message,
            ctaUrl: data.available ? null : form.dataset.ctaUrl,
            ctaLabel: form.dataset.ctaLabel,
        });
    };

    const checkHall = async (form, signal) => {
        const hall = form.querySelector('[name="hall_id"]')?.value;
        const date = form.querySelector('[name="event_date"]')?.value;
        const session = form.querySelector('[name="session_id"]')?.value;
        if (!hall || !date || !session) {
            setStatus(form, {});
            return;
        }
        const url = `/api/availability/hall?hall_id=${encodeURIComponent(hall)}&event_date=${encodeURIComponent(date)}&session_id=${encodeURIComponent(session)}`;
        const res = await fetch(url, { signal, headers: { Accept: 'application/json' } });
        const data = await res.json();
        if (!res.ok) {
            setStatus(form, res.status === 422
                ? { state: 'full', message: data.message || 'Tanggal belum valid.', ctaUrl: form.dataset.ctaUrl, ctaLabel: form.dataset.ctaLabel }
                : {});
            return;
        }
        setStatus(form, {
            state: data.available ? 'ok' : 'full',
            message: data.message,
            ctaUrl: data.available ? null : form.dataset.ctaUrl,
            ctaLabel: form.dataset.ctaLabel,
        });
    };

    document.querySelectorAll('form[data-availability]').forEach((form) => {
        if (form.hasAttribute('data-sold-out')) {
            setStatus(form, { state: 'full', message: form.dataset.soldOutMessage || 'Penuh.', ctaUrl: form.dataset.ctaUrl, ctaLabel: form.dataset.ctaLabel });
            const submit = form.querySelector('[data-availability-submit]');
            if (submit) submit.disabled = true;
            return;
        }

        const kind = form.getAttribute('data-availability');
        const controllers = new Map();
        const run = () => {
            const key = kind;
            controllers.get(key)?.abort();
            const ctrl = new AbortController();
            controllers.set(key, ctrl);
            setStatus(form, { state: 'loading', message: 'Memeriksa ketersediaan…' });
            const task = kind === 'hall' ? checkHall(form, ctrl.signal) : checkRoom(form, ctrl.signal);
            task.catch((e) => {
                if (e?.name !== 'AbortError') setStatus(form, {});
            });
        };
        const debounced = debounce(run, 350);
        form.querySelectorAll('input, select').forEach((el) => {
            el.addEventListener('change', debounced);
            el.addEventListener('input', debounced);
        });
    });
})();
