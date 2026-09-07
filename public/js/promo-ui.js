// Countdown promo (WIB) + rotator headline + tilt 3D kartu + reveal on scroll.
// Vanilla, tanpa dependensi. Gagal aman: tanpa JS, konten promo tetap terbaca.
(() => {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // --- Countdown per kartu [data-valid-until="YYYY-MM-DD"] ---
    const pad = (n) => String(n).padStart(2, '0');
    const tickCountdowns = () => {
        // WIB = UTC+7. Hitung dari UTC agar konsisten lintas timezone browser.
        const nowUtc = Date.now();
        document.querySelectorAll('[data-promo-card][data-valid-until]').forEach((card) => {
            const el = card.querySelector('[data-countdown]');
            if (!el) return;
            const endWib = new Date(`${card.dataset.validUntil}T23:59:59+07:00`).getTime();
            const diff = endWib - nowUtc;
            if (Number.isNaN(endWib) || diff <= 0) {
                el.textContent = 'Berakhir';
                card.classList.add('opacity-60');
                const cta = card.querySelector('a');
                if (cta) cta.classList.add('pointer-events-none', 'opacity-50');
                return;
            }
            const d = Math.floor(diff / 86400000);
            const h = Math.floor((diff % 86400000) / 3600000);
            const m = Math.floor((diff % 3600000) / 60000);
            const s = Math.floor((diff % 60000) / 1000);
            el.textContent = d > 0 ? `${d}h : ${pad(h)} : ${pad(m)} : ${pad(s)}` : `${pad(h)} : ${pad(m)} : ${pad(s)}`;
        });
    };
    tickCountdowns();
    if (!reduceMotion) setInterval(tickCountdowns, 1000);

    // --- Rotator headline hero [data-promo-rotator] ---
    const rotator = document.querySelector('[data-promo-rotator]');
    if (rotator && !reduceMotion) {
        const items = [...rotator.querySelectorAll('[data-promo-headline]')];
        const bar = rotator.querySelector('[data-promo-progress]');
        let i = 0;
        if (items.length > 1) {
            setInterval(() => {
                items[i].classList.add('is-hidden');
                i = (i + 1) % items.length;
                items[i].classList.remove('is-hidden');
                if (bar) {
                    bar.style.transition = 'none';
                    bar.style.width = '0%';
                    requestAnimationFrame(() => {
                        bar.style.transition = 'width 6s linear';
                        bar.style.width = '100%';
                    });
                }
            }, 6000);
            if (bar) {
                bar.style.transition = 'width 6s linear';
                bar.style.width = '100%';
            }
        }
    }

    // --- Tilt 3D kartu promo (pointer halus saja) ---
    if (!reduceMotion && window.matchMedia('(pointer: fine)').matches) {
        document.querySelectorAll('[data-promo-card]').forEach((card) => {
            let raf = null;
            card.addEventListener('pointermove', (e) => {
                const r = card.getBoundingClientRect();
                const x = (e.clientX - r.left) / r.width - 0.5;
                const y = (e.clientY - r.top) / r.height - 0.5;
                cancelAnimationFrame(raf);
                raf = requestAnimationFrame(() => {
                    card.style.transform = `perspective(900px) rotateY(${x * 8}deg) rotateX(${-y * 8}deg) translateY(-4px)`;
                });
            });
            card.addEventListener('pointerleave', () => {
                cancelAnimationFrame(raf);
                card.style.transform = '';
            });
        });
    }

    // --- Reveal on scroll ---
    const revealEls = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window && !reduceMotion) {
        const io = new IntersectionObserver((entries) => {
            entries.forEach((en) => {
                if (en.isIntersecting) {
                    en.target.classList.add('is-visible');
                    io.unobserve(en.target);
                }
            });
        }, { threshold: 0.12 });
        revealEls.forEach((el) => io.observe(el));
    } else {
        revealEls.forEach((el) => el.classList.add('is-visible'));
    }
})();
