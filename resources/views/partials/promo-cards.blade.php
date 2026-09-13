{{-- Kartu promo homepage dengan countdown + tilt 3D. --}}
@php
    $promos = collect($site['promos'] ?? []);
@endphp

@if ($promos->isNotEmpty())
    <section class="mx-auto max-w-container px-5 pt-20 md:px-8" aria-label="Promosi spesial">
        <div class="mb-10 text-center">
            <p class="eyebrow justify-center">Limited offers</p>
            <h2 class="mt-3 font-display text-4xl font-medium tracking-tight text-ink md:text-5xl">Penawaran Spesial</h2>
            <p class="mx-auto mt-4 max-w-xl text-sm leading-relaxed text-muted-text">
                Pengalaman menginap dan berkumpul yang lebih istimewa — untuk waktu terbatas.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-8 md:grid-cols-3" data-promo-grid>
            @foreach ($promos as $promo)
                @php
                    $expired = !empty($promo['valid_until']) && \Carbon\Carbon::parse($promo['valid_until'])->endOfDay()->lt(now());
                    $dark = ($promo['theme'] ?? 'gold') === 'onyx';
                @endphp
                <article
                    class="promo-tilt group relative flex flex-col overflow-hidden rounded-2xl border border-border/70 shadow-card transition-shadow hover:shadow-cardhover {{ $dark ? 'bg-onyx text-background' : 'bg-surface text-ink' }} {{ $expired ? 'opacity-60' : '' }}"
                    data-promo-card @if (!empty($promo['valid_until'])) data-valid-until="{{ $promo['valid_until'] }}" @endif>
                    <div
                        class="pointer-events-none absolute -right-16 -top-16 h-48 w-48 rounded-full bg-navy/20 blur-3xl transition-opacity group-hover:opacity-100">
                    </div>
                    <div class="flex flex-1 flex-col p-7 md:p-8">
                        <div class="flex items-center justify-between gap-3">
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-navy/15 px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-navy-dark">
                                <span class="material-symbols-outlined text-[14px]">local_offer</span>
                                {{ $promo['badge'] }}
                            </span>
                            @if (!empty($promo['valid_until']) && !$expired)
                                <span class="rounded-full bg-danger/10 px-3 py-1 font-mono text-[11px] font-bold text-danger"
                                    data-countdown>…</span>
                            @elseif ($expired)
                                <span
                                    class="rounded-full bg-muted px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-muted-text">Berakhir</span>
                            @endif
                        </div>

                        <h3 class="mt-5 font-display text-2xl font-medium leading-snug">{{ $promo['title'] }}</h3>
                        <p class="mt-3 flex-1 text-sm leading-relaxed {{ $dark ? 'text-background/70' : 'text-muted-text' }}">
                            {{ $promo['desc'] }}</p>

                        @if (!empty($promo['valid_until']) && !$expired)
                            <p class="mt-3 text-xs {{ $dark ? 'text-background/50' : 'text-muted-text/70' }}">
                                Berlaku hingga {{ \Carbon\Carbon::parse($promo['valid_until'])->format('d M Y') }} WIB
                            </p>
                        @endif

                        <a href="{{ route($promo['cta_route']) }}" @if ($expired) aria-disabled="true" tabindex="-1" @endif
                            class="mt-6 inline-flex items-center justify-center gap-2 rounded-full px-6 py-3 text-sm font-semibold transition-all hover:-translate-y-0.5 {{ $dark ? 'bg-navy text-white hover:bg-surface hover:text-ink' : 'bg-ink text-background hover:bg-navy-dark hover:text-white' }} {{ $expired ? 'pointer-events-none opacity-50' : '' }}">
                            {{ $promo['cta_label'] }}
                            <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endif