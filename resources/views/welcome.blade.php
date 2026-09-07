@extends('layouts.app')

@section('title', 'Luxury Hotel & Convention')

@push('head')
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin />
@endpush

@section('content')
    @php
        $catalog = \Illuminate\Support\Facades\Cache::remember('homepage.catalog.v2', 600, function () {
            $roomTypes = \App\Models\RoomType::query()
                ->withCount(['rooms' => fn ($query) => $query->where('status', '!=', 'MAINTENANCE')])
                ->get()
                ->filter(fn ($roomType) => $roomType->rooms_count > 0)
                ->values();
            $activeHalls = \App\Models\Hall::where('is_active', true)->get();

            $toStorable = function ($models) {
                return array_map(function ($item) {
                    if (isset($item['images']) && is_array($item['images'])) {
                        $item['images'] = json_encode($item['images']);
                    }

                    return $item;
                }, $models->toArray());
            };

            return [
                'roomTypes' => $toStorable($roomTypes),
                'activeHalls' => $toStorable($activeHalls),
                'availableRoomsCount' => \App\Models\Room::where('status', '!=', 'MAINTENANCE')->count(),
            ];
        });
        $roomTypes = \App\Models\RoomType::hydrate($catalog['roomTypes']);
        $activeHalls = \App\Models\Hall::hydrate($catalog['activeHalls']);
        $availableRoomsCount = $catalog['availableRoomsCount'];
        $roomTypesCount = $roomTypes->count();
        $maxCapacity = $activeHalls->max('capacity_pax');

        $cover = \App\Models\Hall::where('is_active', true)
            ->get()
            ->merge(\App\Models\RoomType::all())
            ->pluck('images')
            ->flatten()
            ->filter()
            ->first();
        $coverUrl = $cover ? \Illuminate\Support\Facades\Storage::url($cover) : null;
    @endphp

    <!-- Hero -->
    <section class="relative flex min-h-180 items-center overflow-hidden pt-20">
        <div class="absolute inset-0">
            <img class="h-full w-full object-cover"
                src="{{ asset('images/hero.jpg') }}"
                srcset="{{ asset('images/hero-1280.jpg') }} 1280w, {{ asset('images/hero.jpg') }} 1920w"
                sizes="100vw" width="1920" height="1280"
                alt="{{ $site['long_name'] }}" fetchpriority="high" decoding="async" />
            <div class="absolute inset-0 bg-linear-to-b from-onyx/70 via-onyx/40 to-onyx/70"></div>
        </div>

        <canvas id="promo-3d-canvas" class="pointer-events-none absolute inset-0 h-full w-full" aria-hidden="true"></canvas>

        <div class="relative z-10 mx-auto w-full max-w-container px-5 py-24 text-center md:px-8">
            <p class="justify-center text-white">
                <span class="eyebrow h-px w-8 bg-white"></span>
                {{ $site['tagline'] }}
                <span class="eyebrow h-px w-8 bg-white"></span>
            </p>
            <h1 class="mx-auto mt-6 max-w-3xl font-display text-5xl font-medium leading-[1.05] text-background md:text-7xl">
                Refined comfort,<br /><span class="italic text-gold">timeless</span> elegance.
            </h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg font-light leading-relaxed text-background/80">
                Thoughtfully appointed rooms and grand event venues, composed for stays and
                gatherings worth remembering.
            </p>

            @if (!empty($site['promos']))
                <div class="relative mx-auto mt-8 min-h-9 max-w-2xl" data-promo-rotator aria-live="polite">
                    @foreach (collect($site['promos'])->take(3) as $promo)
                        <p data-promo-headline
                            class="promo-headline text-sm font-semibold uppercase tracking-[0.25em] text-white {{ $loop->first ? '' : 'is-hidden' }}">
                            {{ $promo['title'] }}
                        </p>
                    @endforeach
                    <div class="mx-auto mt-3 h-0.5 w-40 overflow-hidden rounded-full bg-background/20">
                        <div class="h-full w-0 bg-white" data-promo-progress></div>
                    </div>
                </div>
            @endif

            <!-- Availability bar -->
            <form action="#rooms" method="GET"
                class="mx-auto mt-12 grid max-w-4xl grid-cols-1 divide-y divide-line/60 overflow-hidden rounded-xl bg-white/95 text-left shadow-card backdrop-blur sm:grid-cols-2 md:grid-cols-4 md:divide-x md:divide-y-0">
                <div class="px-6 py-5">
                    <label class="block text-[11px] font-semibold uppercase tracking-[0.18em] text-stone">Check-in</label>
                    <input type="date" name="check_in_date"
                        class="mt-1 w-full bg-transparent text-sm text-ink outline-none" />
                </div>
                <div class="px-6 py-5">
                    <label class="block text-[11px] font-semibold uppercase tracking-[0.18em] text-stone">Check-out</label>
                    <input type="date" name="check_out_date"
                        class="mt-1 w-full bg-transparent text-sm text-ink outline-none" />
                </div>
                <div class="px-6 py-5">
                    <label class="block text-[11px] font-semibold uppercase tracking-[0.18em] text-stone">Guests</label>
                    <input type="number" min="1" name="guests" placeholder="2"
                        class="mt-1 w-full bg-transparent text-sm text-ink outline-none" />
                </div>
                <button type="submit"
                    class="flex items-center justify-center gap-2 bg-ink px-6 py-5 text-sm font-semibold uppercase tracking-widest text-background transition-colors hover:bg-gold-soft hover:text-white">
                    Search rooms <span class="material-symbols-outlined text-[18px]">search</span>
                </button>
            </form>
        </div>
    </section>

    @include('partials.promo-strip')

    <!-- Notifications -->
    @if ($errors->any())
        <div class="mx-auto max-w-container px-5 md:px-8">
            <div class="mt-8 flex items-start gap-3 rounded-xl border border-danger/20 bg-danger/5 p-4 text-sm text-danger">
                <span class="material-symbols-outlined text-[20px]">error</span>
                <div>
                    <p class="font-semibold">Pemberitahuan Pesanan</p>
                    <ul class="mt-1 list-disc space-y-0.5 pl-5 opacity-90">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Promo cards -->
    <div class="reveal">
        @include('partials.promo-cards')
    </div>

    <!-- Stats strip -->
    <section class="border-b border-line/70 bg-background">
        <div class="mx-auto grid max-w-container grid-cols-2 gap-px md:grid-cols-4">
            <div class="px-5 py-10 text-center md:px-8">
                <p class="font-display text-4xl font-semibold text-ink">{{ $roomTypesCount }}</p>
                <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-stone">Room categories</p>
            </div>
            <div class="px-5 py-10 text-center md:px-8">
                <p class="font-display text-4xl font-semibold text-ink">{{ $availableRoomsCount }}</p>
                <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-stone">Rooms available</p>
            </div>
            <div class="px-5 py-10 text-center md:px-8">
                <p class="font-display text-4xl font-semibold text-ink">{{ $activeHalls->count() }}</p>
                <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-stone">Event venues</p>
            </div>
            <div class="px-5 py-10 text-center md:px-8">
                <p class="font-display text-4xl font-semibold text-ink">{{ number_format($maxCapacity ?? 0, 0, ',', '.') }}</p>
                <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-stone">Max guest capacity</p>
            </div>
        </div>
    </section>

    <!-- Facilities -->
    <section class="reveal mx-auto max-w-container px-5 py-20 md:px-8 md:py-28">
        <div class="mb-12 text-center">
            <p class="eyebrow justify-center">World-class amenities</p>
            <h2 class="mt-3 font-display text-4xl font-medium tracking-tight text-ink md:text-5xl">Fasilitas Hotel</h2>
            <p class="mx-auto mt-4 max-w-xl text-sm leading-relaxed text-stone">
                Segala yang Anda butuhkan untuk bekerja, bersantai, dan merayakan — semuanya dalam satu atap.
            </p>
        </div>
        <div class="grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-4">
    @foreach ($site['facilities'] as $index => $facility)
        <div class="group relative flex flex-col items-center text-center rounded-2xl border border-line/70 bg-surface p-7 transition-shadow duration-300 hover:shadow-cardhover">
            <span aria-hidden="true"
                class="absolute right-5 top-4 font-display text-sm italic text-stone/35 select-none">
                {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
            </span>
            <span class="material-symbols-outlined block text-[26px] text-gold">{{ $facility['icon'] }}</span>
            <h3 class="mt-5 font-display text-lg font-medium text-ink">{{ $facility['title'] }}</h3>
            <p class="mt-2 text-sm leading-relaxed text-stone">{{ $facility['description'] }}</p>
        </div>
    @endforeach
</div>
    </section>

    <!-- Rooms -->
    <section id="rooms" class="reveal mx-auto max-w-container px-5 py-20 md:px-8 md:py-28">
        <div class="mb-12 flex flex-col justify-between gap-6 md:flex-row md:items-end">
            <div>
                <p class="eyebrow">Curated stays</p>
                <h2 class="mt-3 font-display text-4xl font-medium tracking-tight text-ink md:text-5xl">Rooms &amp; Suites</h2>
            </div>
            <a href="{{ route('rooms.index') }}"
                class="group inline-flex items-center gap-1 text-sm font-semibold uppercase tracking-widest text-ink">
                View all <span class="material-symbols-outlined text-[18px] transition-transform group-hover:translate-x-1">arrow_forward</span>
            </a>
        </div>

        @if ($roomTypes->isEmpty())
            <div class="rounded-xl border border-line/70 bg-surface py-16 text-center">
                <span class="material-symbols-outlined text-5xl text-stone/50">hotel_class</span>
                <p class="mt-3 font-display text-xl text-ink">Belum ada kamar yang siap dipesan</p>
                <p class="mt-1 text-sm text-stone">Unit kamar sedang disiapkan oleh pengelola. Silakan cek kembali nanti.</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                @foreach ($roomTypes as $room)
                    @php
                        $roomImages = is_string($room->images) ? json_decode($room->images, true) : $room->images;
                        $firstImage = (!empty($roomImages) && is_array($roomImages)) ? \Illuminate\Support\Facades\Storage::url($roomImages[0]) : 'https://images.unsplash.com/photo-1590490360182-c33d57733427?q=80&w=1170&auto=format&fit=crop';
                    @endphp

                    <div
                        class="group flex flex-col overflow-hidden rounded-2xl border border-line/70 bg-surface shadow-card transition-shadow hover:shadow-cardhover @if ($loop->first) lg:col-span-2 lg:flex-row @endif">
                        <div class="relative min-h-64 overflow-hidden @if ($loop->first) lg:w-1/2 @endif">
                            <img class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                                src="{{ $firstImage }}" alt="{{ $room->name }}" loading="lazy" decoding="async" />
                            @if ($loop->first)
                                <span
                                    class="absolute right-4 top-4 rounded-full bg-gold px-3 py-1 text-[11px] font-semibold uppercase tracking-wider text-white">Signature</span>
                            @endif
                        </div>

                        <div class="flex flex-1 flex-col justify-between p-7 md:p-9">
                            <div>
                                <div class="flex items-start justify-between gap-4">
                                    <h3 class="font-display text-2xl font-medium text-ink">{{ $room->name }}</h3>
                                    <div class="shrink-0 text-right">
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gold-soft">From</p>
                                        <p class="font-display text-xl text-ink">
                                            Rp {{ number_format($room->base_price, 0, ',', '.') }}
                                            <span class="font-body text-xs font-normal text-stone">/ night</span>
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-4 flex flex-wrap gap-x-6 gap-y-2 text-sm text-stone">
                                    <span class="flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-[18px] text-gold">person</span>
                                        Up to {{ $room->max_guests }} guests
                                    </span>
                                    <span class="flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-[18px] text-gold">hotel</span>
                                        @if (($room->rooms_count ?? 0) <= 0)
                                            <span class="rounded-full bg-danger px-2 py-0.5 text-[11px] font-bold uppercase tracking-wider text-white">Penuh</span>
                                        @else
                                            {{ $room->rooms_count }} units
                                        @endif
                                    </span>
                                </div>

                                <p class="mt-5 text-sm leading-relaxed text-stone {{ $loop->first ? 'md:line-clamp-3' : 'line-clamp-3' }}">
                                    {{ $room->description }}
                                </p>
                            </div>

                            <div class="mt-7 flex gap-3">
                                @if (($room->rooms_count ?? 0) <= 0)
                                    <span class="flex-1 rounded-full bg-surface-muted px-6 py-3 text-center text-sm font-semibold text-stone">Penuh</span>
                                @elseif(auth()->check())
                                    <a href="#book-room-{{ $room->id }}" data-toggle-form aria-expanded="false"
                                        class="flex-1 rounded-full bg-ink px-6 py-3 text-center text-sm font-semibold text-background transition-all hover:-translate-y-0.5 hover:bg-gold-soft hover:text-white">Book now</a>
                                @else
                                    <a href="{{ route('login') }}"
                                        class="flex-1 rounded-full bg-ink px-6 py-3 text-center text-sm font-semibold text-background transition-all hover:-translate-y-0.5 hover:bg-gold-soft hover:text-white">Book now</a>
                                @endif
                                <a href="{{ route('rooms.show', $room->id) }}"
                                    class="flex-1 rounded-full border border-line bg-surface px-6 py-3 text-center text-sm font-semibold text-ink transition-colors hover:border-ink">View details</a>
                            </div>

                            @if (($room->rooms_count ?? 0) <= 0)
                                <div class="mt-4 rounded-xl border border-line/70 bg-surface-muted p-4 text-sm text-stone">
                                    Tipe kamar ini sedang penuh.
                                    <a href="{{ route('rooms.index') }}" class="font-semibold text-gold-soft underline underline-offset-4">Lihat kamar lain</a>
                                </div>
                            @else
                            <form id="book-room-{{ $room->id }}" action="{{ route('booking.room.checkout') }}" method="POST"
                                class="inline-book-form mt-6 hidden rounded-xl border border-line/70 bg-surface-muted p-5"
                                data-availability="room"
                                data-cta-url="{{ route('rooms.index') }}"
                                data-cta-label="Lihat kamar lain">
                                @csrf
                                <input type="hidden" name="room_type_id" value="{{ $room->id }}">
                                <input type="hidden" name="number_of_rooms" value="1">
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_auto] lg:items-end">
                                    <div>
                                        <label class="block text-[11px] font-semibold uppercase tracking-wider text-stone">Check-in</label>
                                        <input type="date" name="check_in_date" required min="{{ now()->toDateString() }}"
                                            data-booking-checkin
                                            class="mt-1.5 w-full rounded-lg border border-line bg-background px-3 py-2.5 text-sm outline-none transition-colors focus:border-gold focus:ring-1 focus:ring-gold/30" />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold uppercase tracking-wider text-stone">Check-out</label>
                                        <input type="date" name="check_out_date" required
                                            data-booking-checkout
                                            class="mt-1.5 w-full rounded-lg border border-line bg-background px-3 py-2.5 text-sm outline-none transition-colors focus:border-gold focus:ring-1 focus:ring-gold/30" />
                                    </div>
                                    <button type="submit" data-availability-submit
                                        class="w-full rounded-full bg-ink px-6 py-2.5 text-sm font-semibold text-background transition-all hover:bg-gold-soft hover:text-white disabled:cursor-not-allowed disabled:opacity-50 lg:w-auto">Confirm booking</button>
                                </div>
                                @include('partials.availability-status', ['ctaUrl' => route('rooms.index'), 'ctaLabel' => 'Lihat kamar lain'])
                            </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <!-- Halls -->
    <section id="halls" class="reveal bg-surface-muted">
        <div class="mx-auto max-w-container px-5 py-20 md:px-8 md:py-28">
            <div class="mb-12 flex flex-col justify-between gap-6 md:flex-row md:items-end">
                <div>
                    <p class="eyebrow">Unforgettable gatherings</p>
                    <h2 class="mt-3 font-display text-4xl font-medium tracking-tight text-ink md:text-5xl">Event Venues</h2>
                </div>
                <a href="{{ route('halls.index') }}"
                    class="group inline-flex items-center gap-1 text-sm font-semibold uppercase tracking-widest text-ink">
                    View all <span class="material-symbols-outlined text-[18px] transition-transform group-hover:translate-x-1">arrow_forward</span>
                </a>
            </div>

            @if ($activeHalls->isEmpty())
                <div class="rounded-xl border border-line/70 bg-surface py-16 text-center">
                    <span class="material-symbols-outlined text-5xl text-stone/50">meeting_room</span>
                    <p class="mt-3 font-display text-xl text-ink">Belum ada gedung yang tersedia</p>
                </div>
            @else
                <div class="space-y-16">
                    @foreach ($activeHalls as $hall)
                        @php
                            $hallImages = is_string($hall->images) ? json_decode($hall->images, true) : $hall->images;
                            $firstHallImage = (!empty($hallImages) && is_array($hallImages)) ? \Illuminate\Support\Facades\Storage::url($hallImages[0]) : 'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?q=80&w=1170&auto=format&fit=crop';
                            $reverse = $loop->iteration % 2 === 0;
                        @endphp
                        <div class="flex flex-col items-center gap-8 md:flex-row {{ $reverse ? 'md:flex-row-reverse' : '' }}">
                            <div class="h-72 w-full overflow-hidden rounded-2xl md:h-96 md:w-1/2">
                                <img class="h-full w-full object-cover" src="{{ $firstHallImage }}" alt="{{ $hall->name }}" loading="lazy" decoding="async" />
                            </div>
                            <div class="w-full md:w-1/2 md:px-6">
                                <p class="eyebrow">{{ $reverse ? 'Intimate elegance' : 'Grand scale' }}</p>
                                <h3 class="mt-3 font-display text-3xl font-medium text-ink md:text-4xl">{{ $hall->name }}</h3>
                                <p class="mt-4 text-sm leading-relaxed text-stone">{{ $hall->description }}</p>

                                <div class="mt-6 grid grid-cols-2 gap-6">
                                    <div>
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-stone">Capacity</p>
                                        <p class="mt-1 font-display text-xl text-ink">Up to {{ $hall->capacity_pax }} pax</p>
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-stone">Base rental</p>
                                        <p class="mt-1 font-display text-xl text-ink">
                                            Rp {{ number_format($hall->base_rental_price, 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-8 flex flex-wrap gap-3">
                                    @auth
                                        <a href="#hall-form-{{ $hall->id }}" data-toggle-form aria-expanded="false"
                                            class="rounded-full bg-ink px-6 py-3 text-sm font-semibold text-background transition-all hover:-translate-y-0.5 hover:bg-gold-soft hover:text-white">Inquire now</a>
                                    @else
                                        <a href="{{ route('login') }}"
                                            class="rounded-full bg-ink px-6 py-3 text-sm font-semibold text-background transition-all hover:-translate-y-0.5 hover:bg-gold-soft hover:text-white">Inquire now</a>
                                    @endauth
                                    <a href="{{ route('halls.show', $hall->id) }}"
                                        class="rounded-full border border-line bg-surface px-6 py-3 text-sm font-semibold text-ink transition-colors hover:border-ink">View details</a>
                                </div>

                                <form id="hall-form-{{ $hall->id }}" action="{{ route('booking.hall.checkout') }}" method="POST"
                                    class="inline-book-form mt-8 hidden rounded-xl border border-line/70 bg-surface-muted p-5"
                                    data-availability="hall"
                                    data-cta-url="{{ route('halls.index') }}"
                                    data-cta-label="Lihat gedung lain">
                                    @csrf
                                    <input type="hidden" name="hall_id" value="{{ $hall->id }}">
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <label class="block text-[11px] font-semibold uppercase tracking-wider text-stone">Tanggal acara</label>
                                            <input type="date" name="event_date" required min="{{ now()->toDateString() }}"
                                                class="mt-1.5 w-full rounded-lg border border-line bg-background px-3 py-2.5 text-sm outline-none transition-colors focus:border-gold focus:ring-1 focus:ring-gold/30" />
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold uppercase tracking-wider text-stone">Sesi waktu</label>
                                            <select name="session_id" required
                                                class="mt-1.5 w-full rounded-lg border border-line bg-background px-3 py-2.5 text-sm outline-none transition-colors focus:border-gold focus:ring-1 focus:ring-gold/30">
                                                @foreach (\App\Models\HallSession::all() as $session)
                                                    <option value="{{ $session->id }}">{{ $session->session_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <label class="block text-[11px] font-semibold uppercase tracking-wider text-stone">Jenis acara</label>
                                        <input type="text" name="event_type" placeholder="Pernikahan / Wisuda / Rapat corporate" required maxlength="100"
                                            class="mt-1.5 w-full rounded-lg border border-line bg-background px-3 py-2.5 text-sm outline-none transition-colors focus:border-gold focus:ring-1 focus:ring-gold/30" />
                                    </div>
                                    @include('partials.availability-status', ['ctaUrl' => route('halls.index'), 'ctaLabel' => 'Lihat gedung lain'])
                                    <button type="submit" data-availability-submit
                                        class="mt-5 w-full rounded-full bg-ink px-6 py-3 text-sm font-semibold text-background transition-all hover:bg-gold-soft hover:text-white disabled:cursor-not-allowed disabled:opacity-50">Sewa gedung ini</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <!-- Testimonials -->
    <section class="reveal overflow-hidden border-y border-line/70 bg-surface-muted">
        <div class="mx-auto max-w-container px-5 py-20 md:px-8 md:py-28">
            <div class="mb-12 flex flex-col justify-between gap-6 md:flex-row md:items-end">
                <div>
                    <p class="eyebrow">Guest stories</p>
                    <h2 class="mt-3 font-display text-4xl font-medium tracking-tight text-ink md:text-5xl">Kata Mereka</h2>
                </div>
                <div class="flex items-center gap-2 text-sm text-stone">
                    <span class="material-symbols-outlined text-[18px] text-star" style="font-variation-settings:'FILL' 1;">star</span>
                    <span><span class="font-semibold text-ink">Guest rating 4.9 / 5</span></span>
                </div>
            </div>

            <div class="hide-scrollbar -mx-5 flex snap-x snap-mandatory gap-6 overflow-x-auto px-5 pb-2 md:-mx-8 md:px-8">
                @foreach ($site['testimonials'] as $testimonial)
                    <figure
                        class="flex w-[85%] shrink-0 snap-center flex-col rounded-2xl border border-line/70 bg-surface p-8 sm:w-[46%] lg:w-[31.5%]">
                        <span aria-hidden="true" class="font-display text-4xl leading-none text-gold/50">&ldquo;</span>
                        <blockquote class="mt-2 grow font-display text-lg italic leading-relaxed text-ink/85">
                            {{ $testimonial['text'] }}
                        </blockquote>
                        <div class="mt-5 flex gap-0.5" aria-label="Rating {{ $testimonial['rating'] }} dari 5">
                            @for ($i = 0; $i < $testimonial['rating']; $i++)
                                <span class="material-symbols-outlined text-[16px] text-star" style="font-variation-settings:'FILL' 1;">star</span>
                            @endfor
                            @for ($i = $testimonial['rating']; $i < 5; $i++)
                                <span class="material-symbols-outlined text-[16px] text-stone/25">star</span>
                            @endfor
                        </div>
                        <figcaption class="mt-5 flex items-center gap-3 border-t border-line/70 pt-5">
                            <span
                                class="flex h-9 w-9 items-center justify-center rounded-full bg-onyx font-display text-sm font-semibold text-background">
                                {{ strtoupper(substr($testimonial['name'], 0, 1)) }}
                            </span>
                            <span>
                                <span class="block text-sm font-semibold text-ink">{{ $testimonial['name'] }}</span>
                                <span class="block text-xs text-stone">{{ $testimonial['origin'] }}</span>
                            </span>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </div>
    </section>

    <!-- About / Contact -->
    <section id="about" class="reveal mx-auto max-w-container px-5 py-20 md:px-8 md:py-28">
        <div class="grid grid-cols-1 gap-12 lg:grid-cols-2 lg:items-center">
            <div class="relative">
                <div class="overflow-hidden rounded-2xl">
                    <img class="h-105 w-full object-cover"
                        src="{{ 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?q=80&w=1170&auto=format&fit=crop' }}"
                        alt="{{ $site['long_name'] }}" loading="lazy" decoding="async" />
                </div>
                <div
                    class="absolute -bottom-6 left-6 rounded-2xl border border-line/70 bg-surface px-6 py-4 shadow-card">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gold-soft">Est. years of hospitality</p>
                    <p class="mt-0.5 font-display text-2xl font-semibold text-ink">Where every stay is considered</p>
                </div>
            </div>

            <div>
                <p class="eyebrow">The {{ $site['name'] }} experience</p>
                <h2 class="mt-3 font-display text-4xl font-medium leading-tight tracking-tight text-ink md:text-5xl">
                    A sanctuary of comfort in the heart of the city
                </h2>
                <p class="mt-6 text-sm leading-relaxed text-stone">
                    From thoughtfully designed rooms to grand ballrooms, every detail is composed to
                    let you unwind, celebrate, and connect — supported around the clock by our hospitality team.
                </p>

                <div class="mt-8 space-y-4">
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined mt-0.5 text-[20px] text-gold">location_on</span>
                        <div>
                            <p class="text-sm font-semibold text-ink">Address</p>
                            <p class="mt-0.5 text-sm text-stone">{{ $site['address'] }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined mt-0.5 text-[20px] text-gold">call</span>
                        <div>
                            <p class="text-sm font-semibold text-ink">Reservations</p>
                            <p class="mt-0.5 text-sm text-stone">
                                <a href="tel:{{ $site['phone'] }}" class="hover:text-gold-soft">{{ $site['phone'] }}</a> ·
                                <a href="mailto:{{ $site['email'] }}" class="hover:text-gold-soft">{{ $site['email'] }}</a>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined mt-0.5 text-[20px] text-gold">schedule</span>
                        <div>
                            <p class="text-sm font-semibold text-ink">Opening hours</p>
                            <p class="mt-0.5 text-sm text-stone">{{ $site['hours'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-10 flex flex-wrap gap-3">
                    <a href="{{ route('contact') }}"
                        class="inline-flex items-center gap-2 rounded-full bg-ink px-7 py-3 text-sm font-semibold text-background transition-all hover:-translate-y-0.5 hover:bg-gold-soft hover:text-white">
                        <span class="material-symbols-outlined text-[18px]">location_on</span> Lokasi &amp; Hubungi Kami
                    </a>
                    <a href="{{ route('halls.index') }}"
                        class="inline-flex items-center gap-2 rounded-full border border-line bg-surface px-7 py-3 text-sm font-semibold text-ink transition-colors hover:border-ink">
                        Lihat Gedung Acara
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('js/availability.js') }}" defer></script>
    <script src="{{ asset('js/promo-ui.js') }}" defer></script>
    {{-- 3D hero hanya di desktop + koneksi cepat; mobile hemat ~600KB Three.js. Foto hero tetap tampil. --}}
    <script>
        (function () {
            var conn = navigator.connection || {};
            var saveData = !!conn.saveData;
            var slow = /^(slow-2g|2g|3g)$/.test(conn.effectiveType || '');
            var mobile = window.matchMedia('(max-width: 768px)').matches;
            if (saveData || slow || mobile) return;
            var load = function () {
                var s = document.createElement('script');
                s.type = 'module';
                s.src = "{{ asset('js/promo-3d.js') }}";
                document.body.appendChild(s);
            };
            if ('requestIdleCallback' in window) requestIdleCallback(load, { timeout: 3000 });
            else window.addEventListener('load', load);
        })();
    </script>
    <script>
        // Toggle form booking inline (Book now / Inquire now) di homepage.
        document.querySelectorAll('[data-toggle-form]').forEach(function (link) {
            link.addEventListener('click', function (event) {
                event.preventDefault();

                const target = document.querySelector(link.getAttribute('href'));
                if (!target) return;

                const willShow = target.classList.contains('hidden');

                // Tutup semua form inline lain agar tidak terbuka bersamaan.
                document.querySelectorAll('.inline-book-form').forEach(function (form) {
                    if (form !== target) {
                        form.classList.add('hidden');
                    }
                });
                document.querySelectorAll('[data-toggle-form]').forEach(function (other) {
                    if (other !== link) {
                        other.setAttribute('aria-expanded', 'false');
                    }
                });

                target.classList.toggle('hidden', !willShow);
                link.setAttribute('aria-expanded', willShow ? 'true' : 'false');

                if (willShow) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    const firstField = target.querySelector('input:not([type=hidden]), select');
                    if (firstField) {
                        firstField.focus({ preventScroll: true });
                    }
                }
            });
        });

        // Check-out minimal H+1 dari check-in.
        document.querySelectorAll('[data-booking-checkin]').forEach(function (checkin) {
            const form = checkin.closest('form');
            const checkout = form ? form.querySelector('[data-booking-checkout]') : null;
            if (!checkout) return;

            const syncMin = function () {
                if (!checkin.value) return;
                const next = new Date(checkin.value);
                next.setDate(next.getDate() + 1);
                checkout.min = next.toISOString().slice(0, 10);
                if (checkout.value && checkout.value <= checkin.value) {
                    checkout.value = '';
                }
            };

            checkin.addEventListener('change', syncMin);
            syncMin();
        });
    </script>
@endpush
