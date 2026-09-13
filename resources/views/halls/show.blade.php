@extends('layouts.app')

@section('title', $hall->name)

@section('content')
    @php
        $hallImages = is_string($hall->images) ? json_decode($hall->images, true) : $hall->images;
        $hallImages = (!empty($hallImages) && is_array($hallImages) && count($hallImages) > 0) ? $hallImages : [null];
    @endphp

    <section class="mx-auto max-w-container px-5 pt-28 md:px-8">
        <nav class="flex items-center gap-2 text-xs text-muted-text">
            <a href="/" class="transition-colors hover:text-ink">Home</a>
            <span>/</span>
            <a href="{{ route('halls.index') }}" class="transition-colors hover:text-ink">Venues</a>
            <span>/</span>
            <span class="font-semibold text-ink">{{ $hall->name }}</span>
        </nav>

        <!-- Gallery -->
        <div class="mt-6 grid h-[380px] grid-cols-1 gap-2 overflow-hidden rounded-2xl md:h-[520px] md:grid-cols-4 md:grid-rows-2">
            <div class="h-full w-full md:col-span-2 md:row-span-2">
                <img class="h-full w-full object-cover transition-transform duration-700 hover:scale-105"
                    src="{{ \App\Support\HotelImage::url($hallImages[0] ?? null, 'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?q=80&w=1170&auto=format&fit=crop') }}"
                    alt="{{ $hall->name }}" fetchpriority="high" decoding="async" />
            </div>
            @for ($i = 1; $i <= 4; $i++)
                @if (isset($hallImages[$i]) && $hallImages[$i])
                    <div class="hidden h-full w-full md:block">
                        <img class="h-full w-full object-cover transition-transform duration-700 hover:scale-105"
                            src="{{ \App\Support\HotelImage::url($hallImages[$i]) }}"
                            alt="{{ $hall->name }}" loading="lazy" decoding="async" />
                    </div>
                @endif
            @endfor
        </div>
    </section>

    <section class="mx-auto max-w-container px-5 py-14 md:px-8">
        <div class="grid grid-cols-1 gap-12 lg:grid-cols-3">
            <!-- Details -->
            <div class="lg:col-span-2">
                <p class="eyebrow">Unforgettable gatherings</p>
                <h1 class="mt-3 font-display text-4xl font-medium tracking-tight text-ink md:text-5xl">{{ $hall->name }}</h1>

                <div class="mt-6 flex flex-wrap gap-x-8 gap-y-3 border-b border-border/70 pb-8 text-sm text-muted-text">
                    <span class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px] text-navy">groups</span>
                        Up to {{ $hall->capacity_pax }} pax
                    </span>
                    <span class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px] text-navy">payments</span>
                        Rp {{ number_format($hall->base_rental_price, 0, ',', '.') }} / session
                    </span>
                </div>

                <h2 class="mt-8 font-display text-2xl font-medium text-ink">About this venue</h2>
                <p class="mt-4 max-w-2xl text-sm leading-relaxed text-muted-text">{{ $hall->description }}</p>

                @if (isset($sessions) && count($sessions) > 0)
                    <h2 class="mt-10 font-display text-2xl font-medium text-ink">Available sessions</h2>
                    <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        @foreach ($sessions as $session)
                            <div class="flex items-center justify-between rounded-xl border border-border/70 bg-muted px-5 py-4">
                                <div>
                                    <p class="text-sm font-semibold text-ink">{{ $session->session_name }}</p>
                                    <p class="mt-1 text-xs text-muted-text">
                                        {{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} –
                                        {{ \Carbon\Carbon::parse($session->end_time)->format('H:i') }} WIB
                                    </p>
                                </div>
                                <span class="material-symbols-outlined text-navy">schedule</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Reservation card -->
            <div class="lg:col-span-1">
                <div class="rounded-2xl border border-border/70 bg-surface p-7 shadow-card lg:sticky lg:top-28">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-navy-soft">Base rental</p>
                    <p class="mt-1 font-display text-3xl font-semibold text-ink">
                        Rp {{ number_format($hall->base_rental_price, 0, ',', '.') }}
                        <span class="font-body text-sm font-normal text-muted-text">/ session</span>
                    </p>

                    @include('partials.booking-error')

                    @if (!isset($sessions) || count($sessions) === 0)
                        <div class="mt-7 rounded-xl border border-border/70 bg-muted p-5 text-center">
                            <span class="material-symbols-outlined text-4xl text-muted-text/40">meeting_room</span>
                            <p class="mt-2 font-display text-xl text-ink">Belum ada sesi tersedia</p>
                            <p class="mt-1 text-sm text-muted-text">Silakan cek kembali nanti.</p>
                            <a href="{{ route('halls.index') }}"
                                class="mt-4 inline-block rounded-full bg-ink px-6 py-3 text-sm font-semibold text-background transition-colors hover:bg-navy-dark hover:text-white">Lihat gedung lain</a>
                        </div>
                    @else
                        <form action="{{ route('booking.hall.checkout') }}" method="POST" class="mt-7 space-y-4"
                            data-availability="hall"
                            data-cta-url="{{ route('halls.index') }}"
                            data-cta-label="Lihat gedung lain">
                            @csrf
                            <input type="hidden" name="hall_id" value="{{ $hall->id }}">
                            <div>
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-muted-text">Tanggal acara</label>
                                <input type="date" name="event_date" required min="{{ now()->toDateString() }}"
                                    value="{{ old('event_date') }}"
                                    class="mt-1 w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm outline-none focus:border-navy" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-muted-text">Sesi waktu</label>
                                <select name="session_id" required
                                    class="mt-1 w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm outline-none focus:border-navy">
                                    @foreach ($sessions as $session)
                                        <option value="{{ $session->id }}" @selected(old('session_id') == $session->id)>{{ $session->session_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-muted-text">Jenis acara</label>
                                <select name="event_type" required
                                    class="mt-1 w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm outline-none focus:border-navy">
                                    <option value="" disabled @selected(!old('event_type'))>Pilih jenis acara</option>
                                    @include('partials.event-type-options')
                                </select>
                            </div>

                            @include('partials.availability-status', ['ctaUrl' => route('halls.index'), 'ctaLabel' => 'Lihat gedung lain'])

                            @auth
                                <button type="submit" data-availability-submit
                                    class="w-full rounded-full bg-ink px-6 py-3.5 text-sm font-semibold text-background transition-colors hover:bg-navy-dark hover:text-white disabled:cursor-not-allowed disabled:opacity-50">Sewa gedung ini</button>
                            @else
                                <a href="{{ route('login') }}"
                                    class="block w-full rounded-full bg-ink px-6 py-3.5 text-center text-sm font-semibold text-background transition-colors hover:bg-navy-dark hover:text-white">Sign in to book</a>
                            @endauth
                        </form>
                    @endif

                    <p class="mt-4 text-center text-xs text-muted-text">Our event team will contact you to confirm final arrangements.</p>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('js/availability.js') }}" defer></script>
@endpush