@extends('layouts.app')

@section('title', 'Rooms & Suites')

@section('content')
    <section class="bg-onyx pb-16 pt-36 text-background">
        <div class="mx-auto max-w-container px-5 md:px-8">
            <p class="eyebrow text-navy">Curated accommodations</p>
            <h1 class="mt-4 font-display text-5xl font-medium tracking-tight md:text-6xl">Rooms &amp; Suites</h1>
            <p class="mt-5 max-w-2xl text-base font-light leading-relaxed text-background/70">
                A considered collection of well-appointed rooms, each finished with a calm palette,
                premium fabrics, and the essentials of a restful stay.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-container px-5 py-16 md:px-8 md:py-20">
        @include('partials.booking-error')
        @if ($rooms->count() === 0)
            <div class="rounded-2xl border border-border/70 bg-surface py-20 text-center">
                <span class="material-symbols-outlined text-5xl text-muted-text/40">hotel_class</span>
                <p class="mt-4 font-display text-2xl text-ink">Belum ada kamar yang tersedia</p>
                <p class="mt-2 text-sm text-muted-text">Silakan cek kembali nanti.</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($rooms as $room)
                    @php
                        $roomImages = is_string($room->images) ? json_decode($room->images, true) : $room->images;
                        $roomImages = (!empty($roomImages) && is_array($roomImages) && count($roomImages) > 0) ? $roomImages : [null];
                    @endphp

                    <div
                        class="group flex flex-col overflow-hidden rounded-2xl border border-border/70 bg-surface shadow-card transition-shadow hover:shadow-cardhover">
                        <a href="{{ route('rooms.show', $room->id) }}" class="relative block h-60 overflow-hidden">
                            <div class="hide-scrollbar flex h-full w-full snap-x snap-mandatory overflow-x-auto">
                                @foreach ($roomImages as $img)
                                    <div class="h-full w-full flex-shrink-0 snap-center">
                                        <img class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                                            src="{{ \App\Support\HotelImage::url($img, 'https://images.unsplash.com/photo-1590490360182-c33d57733427?q=80&w=1170&auto=format&fit=crop') }}"
                                            alt="{{ $room->name }}" loading="lazy" decoding="async" />
                                    </div>
                                @endforeach
                            </div>
                            <span
                                class="absolute right-4 top-4 rounded-full px-3 py-1 text-[11px] font-semibold uppercase tracking-wider backdrop-blur {{ ($room->rooms_count ?? 0) <= 0 ? 'bg-danger text-white' : 'bg-background/85 text-ink' }}">
                                {{ ($room->rooms_count ?? 0) <= 0 ? 'Penuh' : $room->rooms_count . ' units' }}
                            </span>
                        </a>

                        <div class="flex flex-1 flex-col p-6">
                            <a href="{{ route('rooms.show', $room->id) }}">
                                <h2 class="font-display text-2xl font-medium text-ink transition-colors hover:text-navy-soft">{{ $room->name }}</h2>
                            </a>
                            <p class="mt-3 line-clamp-3 text-sm leading-relaxed text-muted-text">{{ $room->description }}</p>

                            <span class="mt-6 flex items-center gap-1.5 text-sm text-muted-text">
                                <span class="material-symbols-outlined text-[18px] text-navy">person</span>
                                Up to {{ $room->max_guests }} guests
                            </span>

                            <div class="mt-5 flex items-end justify-between border-t border-border/70 pt-5">
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-navy-soft">From</p>
                                    <p class="font-display text-xl text-ink">
                                        Rp {{ number_format($room->base_price, 0, ',', '.') }}
                                        <span class="font-body text-xs font-normal text-muted-text">/ night</span>
                                    </p>
                                </div>
                                <a href="{{ route('rooms.show', $room->id) }}"
                                    class="inline-flex items-center gap-1 text-sm font-semibold text-ink transition-colors hover:text-navy-soft">
                                    Details <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                                </a>
                            </div>

                            <form action="{{ route('booking.room.checkout') }}" method="POST" class="mt-5 space-y-3"
                                data-availability="room"
                                data-cta-url="{{ route('rooms.index') }}"
                                data-cta-label="Lihat kamar lain"
                                @if (($room->rooms_count ?? 0) <= 0) data-sold-out data-sold-out-message="Tipe kamar ini sedang penuh." @endif>
                                @csrf
                                <input type="hidden" name="room_type_id" value="{{ $room->id }}">
                                <input type="hidden" name="number_of_rooms" value="1">
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-semibold uppercase tracking-wider text-muted-text">Check-in</label>
                                        <input type="date" name="check_in_date" required min="{{ now()->toDateString() }}"
                                            value="{{ old('check_in_date') }}"
                                            data-booking-checkin
                                            @if (($room->rooms_count ?? 0) <= 0) disabled @endif
                                            class="mt-1 w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-navy disabled:opacity-50" />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold uppercase tracking-wider text-muted-text">Check-out</label>
                                        <input type="date" name="check_out_date" required min="{{ now()->toDateString() }}"
                                            value="{{ old('check_out_date') }}"
                                            data-booking-checkout
                                            @if (($room->rooms_count ?? 0) <= 0) disabled @endif
                                            class="mt-1 w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-navy disabled:opacity-50" />
                                    </div>
                                </div>
                                @include('partials.availability-status', ['ctaUrl' => route('rooms.index'), 'ctaLabel' => 'Lihat kamar lain'])
                                @if (($room->rooms_count ?? 0) <= 0)
                                    <div class="rounded-xl border border-border/70 bg-muted p-3 text-sm text-muted-text">
                                        Tipe kamar ini sedang penuh.
                                        <a href="{{ route('rooms.index') }}" class="font-semibold text-navy-soft underline underline-offset-4">Lihat kamar lain</a>
                                    </div>
                                @endif
                                @auth
                                    <button type="submit" data-availability-submit
                                        @if (($room->rooms_count ?? 0) <= 0) disabled @endif
                                        class="w-full rounded-full bg-ink px-6 py-3 text-sm font-semibold text-background transition-colors hover:bg-navy-dark hover:text-white disabled:cursor-not-allowed disabled:opacity-50">Book this room</button>
                                @else
                                    <a href="{{ route('login') }}"
                                        class="block w-full rounded-full bg-ink px-6 py-3 text-center text-sm font-semibold text-background transition-colors hover:bg-navy-dark hover:text-white">Sign in to book</a>
                                @endauth
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('js/availability.js') }}" defer></script>
@endpush