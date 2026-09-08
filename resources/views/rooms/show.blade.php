@extends('layouts.app')

@section('title', $roomType->name)

@section('content')
    @php
        $roomImages = is_string($roomType->images) ? json_decode($roomType->images, true) : $roomType->images;
        $roomImages = (!empty($roomImages) && is_array($roomImages) && count($roomImages) > 0) ? $roomImages : [null];
    @endphp

    <section class="mx-auto max-w-container px-5 pt-28 md:px-8">
        <nav class="flex items-center gap-2 text-xs text-stone">
            <a href="/" class="transition-colors hover:text-ink">Home</a>
            <span>/</span>
            <a href="{{ route('rooms.index') }}" class="transition-colors hover:text-ink">Rooms</a>
            <span>/</span>
            <span class="font-semibold text-ink">{{ $roomType->name }}</span>
        </nav>

        <!-- Gallery -->
        <div class="mt-6 grid h-[380px] grid-cols-1 gap-2 overflow-hidden rounded-2xl md:h-[520px] md:grid-cols-4 md:grid-rows-2">
            <div class="h-full w-full md:col-span-2 md:row-span-2">
                <img class="h-full w-full object-cover transition-transform duration-700 hover:scale-105"
                    src="{{ \App\Support\HotelImage::url($roomImages[0] ?? null, 'https://images.unsplash.com/photo-1590490360182-c33d57733427?q=80&w=1170&auto=format&fit=crop') }}"
                    alt="{{ $roomType->name }}" fetchpriority="high" decoding="async" />
            </div>
            @for ($i = 1; $i <= 4; $i++)
                @if (isset($roomImages[$i]) && $roomImages[$i])
                    <div class="hidden h-full w-full md:block">
                        <img class="h-full w-full object-cover transition-transform duration-700 hover:scale-105"
                            src="{{ \App\Support\HotelImage::url($roomImages[$i]) }}"
                            alt="{{ $roomType->name }}" loading="lazy" decoding="async" />
                    </div>
                @endif
            @endfor
        </div>
    </section>

    <section class="mx-auto max-w-container px-5 py-14 md:px-8">
        <div class="grid grid-cols-1 gap-12 lg:grid-cols-3">
            <!-- Details -->
            <div class="lg:col-span-2">
                <p class="eyebrow">Curated stays</p>
                <h1 class="mt-3 font-display text-4xl font-medium tracking-tight text-ink md:text-5xl">{{ $roomType->name }}</h1>

                <div class="mt-6 flex flex-wrap gap-x-8 gap-y-3 border-b border-line/70 pb-8 text-sm text-stone">
                    <span class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px] text-gold">person</span>
                        Up to {{ $roomType->max_guests }} guests
                    </span>
                    <span class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px] text-gold">hotel</span>
                        {{ $roomType->total_inventory }} units available
                    </span>
                    <span class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px] text-gold">payments</span>
                        Rp {{ number_format($roomType->base_price, 0, ',', '.') }} / night
                    </span>
                </div>

                <h2 class="mt-8 font-display text-2xl font-medium text-ink">About this room</h2>
                <p class="mt-4 max-w-2xl text-sm leading-relaxed text-stone">{{ $roomType->description }}</p>

                <div class="mt-10 rounded-2xl border border-line/70 bg-surface-muted p-6 md:p-8">
                    <h2 class="font-display text-2xl font-medium text-ink">Good to know</h2>
                    <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-3">
                        <div>
                            <span class="material-symbols-outlined text-[22px] text-gold">schedule</span>
                            <p class="mt-2 text-sm font-semibold text-ink">Check-in / out</p>
                            <p class="mt-1 text-xs leading-relaxed text-stone">Check-in from 14:00<br />Check-out by 12:00</p>
                        </div>
                        <div>
                            <span class="material-symbols-outlined text-[22px] text-gold">shield_check</span>
                            <p class="mt-2 text-sm font-semibold text-ink">Cancellation</p>
                            <p class="mt-1 text-xs leading-relaxed text-stone">Free cancellation up to 48 hours before check-in.</p>
                        </div>
                        <div>
                            <span class="material-symbols-outlined text-[22px] text-gold">support_agent</span>
                            <p class="mt-2 text-sm font-semibold text-ink">Front desk</p>
                            <p class="mt-1 text-xs leading-relaxed text-stone">Concierge &amp; room service available 24/7.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking card -->
            <div class="lg:col-span-1">
                <div class="rounded-2xl border border-line/70 bg-surface p-7 shadow-card lg:sticky lg:top-28">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gold-soft">From</p>
                    <p class="mt-1 font-display text-3xl font-semibold text-ink">
                        Rp {{ number_format($roomType->base_price, 0, ',', '.') }}
                        <span class="font-body text-sm font-normal text-stone">/ night</span>
                    </p>

                    @include('partials.booking-error')

                    @if (($roomType->total_inventory ?? 0) <= 0)
                        <div class="mt-7 rounded-xl border border-danger/20 bg-danger/5 p-5 text-center">
                            <span class="material-symbols-outlined text-4xl text-danger/60">hotel_class</span>
                            <p class="mt-2 font-display text-xl text-ink">Tipe kamar ini sedang penuh</p>
                            <p class="mt-1 text-sm text-stone">Silakan pilih tipe lain yang tersedia.</p>
                            <a href="{{ route('rooms.index') }}"
                                class="mt-4 inline-block rounded-full bg-ink px-6 py-3 text-sm font-semibold text-background transition-colors hover:bg-gold-soft hover:text-white">Lihat kamar lain</a>
                        </div>
                    @else
                        <form action="{{ route('booking.room.checkout') }}" method="POST" class="mt-7 space-y-4"
                            data-availability="room"
                            data-cta-url="{{ route('rooms.index') }}"
                            data-cta-label="Lihat kamar lain">
                            @csrf
                            <input type="hidden" name="room_type_id" value="{{ $roomType->id }}">
                            <div>
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-stone">Check-in</label>
                                <input type="date" name="check_in_date" required min="{{ now()->toDateString() }}"
                                    value="{{ old('check_in_date') }}"
                                    data-booking-checkin
                                    class="mt-1 w-full rounded-lg border border-line bg-background px-3 py-2.5 text-sm outline-none focus:border-gold" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-stone">Check-out</label>
                                <input type="date" name="check_out_date" required min="{{ now()->toDateString() }}"
                                    value="{{ old('check_out_date') }}"
                                    data-booking-checkout
                                    class="mt-1 w-full rounded-lg border border-line bg-background px-3 py-2.5 text-sm outline-none focus:border-gold" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-stone">Rooms (max 5)</label>
                                <input type="number" name="number_of_rooms" value="{{ old('number_of_rooms', 1) }}" min="1" max="5"
                                    class="mt-1 w-full rounded-lg border border-line bg-background px-3 py-2.5 text-sm outline-none focus:border-gold" />
                            </div>

                            @include('partials.availability-status', ['ctaUrl' => route('rooms.index'), 'ctaLabel' => 'Lihat kamar lain'])

                            @auth
                                <button type="submit" data-availability-submit
                                    class="w-full rounded-full bg-ink px-6 py-3.5 text-sm font-semibold text-background transition-colors hover:bg-gold-soft hover:text-white disabled:cursor-not-allowed disabled:opacity-50">Book this room</button>
                            @else
                                <a href="{{ route('login') }}"
                                    class="block w-full rounded-full bg-ink px-6 py-3.5 text-center text-sm font-semibold text-background transition-colors hover:bg-gold-soft hover:text-white">Sign in to book</a>
                            @endauth
                        </form>
                    @endif

                    <p class="mt-4 text-center text-xs text-stone">Free cancellation available up to 48 hours before check-in.</p>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('js/availability.js') }}" defer></script>
@endpush