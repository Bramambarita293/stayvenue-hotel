@extends('layouts.app')

@section('title', 'Luxury Hotel & Convention')

@section('content')
    @php
        $roomTypes = \App\Models\RoomType::all()->filter(fn($room) => $room->total_inventory > 0);
        $roomTypes = $roomTypes->values();
        $activeHalls = \App\Models\Hall::where('is_active', true)->get();
        $availableRoomsCount = \App\Models\Room::where('status', '!=', 'MAINTENANCE')->count();
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
    <section class="relative flex min-h-[720px] items-center overflow-hidden pt-20">
        <div class="absolute inset-0">
            <img class="h-full w-full object-cover"
                src="{{ $coverUrl ?? 'https://images.unsplash.com/photo-1566073771259-6a8506099945?q=80&w=1920&auto=format&fit=crop' }}"
                alt="{{ $site['long_name'] }}" />
            <div class="absolute inset-0 bg-gradient-to-b from-onyx/70 via-onyx/40 to-onyx/70"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-container px-5 py-24 text-center md:px-8">
            <p class="eyebrow justify-center text-gold">
                <span class="h-px w-8 bg-gold/60"></span>
                {{ $site['tagline'] }}
                <span class="h-px w-8 bg-gold/60"></span>
            </p>
            <h1 class="mx-auto mt-6 max-w-3xl font-display text-5xl font-medium leading-[1.05] text-background md:text-7xl">
                Refined comfort,<br /><span class="italic text-gold">timeless</span> elegance.
            </h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg font-light leading-relaxed text-background/80">
                Thoughtfully appointed rooms and grand event venues, composed for stays and
                gatherings worth remembering.
            </p>

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

    <!-- Rooms -->
    <section id="rooms" class="mx-auto max-w-container px-5 py-20 md:px-8 md:py-28">
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
                                src="{{ $firstImage }}" alt="{{ $room->name }}" />
                            @if ($loop->first)
                                <span
                                    class="absolute right-4 top-4 rounded-full bg-gold px-3 py-1 text-[11px] font-semibold uppercase tracking-wider text-onyx">Signature</span>
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
                                        {{ $room->total_inventory }} units
                                    </span>
                                </div>

                                <p class="mt-5 text-sm leading-relaxed text-stone {{ $loop->first ? 'md:line-clamp-3' : 'line-clamp-3' }}">
                                    {{ $room->description }}
                                </p>
                            </div>

                            <div class="mt-7 flex gap-3">
                                @auth
                                    <a href="#book-room-{{ $room->id }}"
                                        class="flex-1 rounded-full bg-ink px-6 py-3 text-center text-sm font-semibold text-background transition-all hover:-translate-y-0.5 hover:bg-gold-soft hover:text-white">Book now</a>
                                @else
                                    <a href="{{ route('login') }}"
                                        class="flex-1 rounded-full bg-ink px-6 py-3 text-center text-sm font-semibold text-background transition-all hover:-translate-y-0.5 hover:bg-gold-soft hover:text-white">Book now</a>
                                @endauth
                                <a href="{{ route('rooms.show', $room->id) }}"
                                    class="flex-1 rounded-full border border-line bg-surface px-6 py-3 text-center text-sm font-semibold text-ink transition-colors hover:border-ink">View details</a>
                            </div>

                            <form id="book-room-{{ $room->id }}" action="{{ route('booking.room.checkout') }}" method="POST"
                                class="mt-6 hidden flex-wrap items-end gap-3 border-t border-line/70 pt-5">
                                @csrf
                                <input type="hidden" name="room_type_id" value="{{ $room->id }}">
                                <input type="hidden" name="number_of_rooms" value="1">
                                <div>
                                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-stone">Check-in</label>
                                    <input type="date" name="check_in_date" required
                                        class="mt-1 rounded-lg border border-line bg-background px-3 py-2 text-sm outline-none focus:border-gold" />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-stone">Check-out</label>
                                    <input type="date" name="check_out_date" required
                                        class="mt-1 rounded-lg border border-line bg-background px-3 py-2 text-sm outline-none focus:border-gold" />
                                </div>
                                <button type="submit"
                                    class="rounded-full bg-gold px-6 py-2.5 text-sm font-semibold text-onyx transition-colors hover:bg-gold-soft hover:text-white">Confirm booking</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <!-- Halls -->
    <section id="halls" class="bg-surface-muted">
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
                                <img class="h-full w-full object-cover" src="{{ $firstHallImage }}" alt="{{ $hall->name }}" />
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
                                        <a href="#hall-form-{{ $hall->id }}"
                                            class="rounded-full bg-ink px-6 py-3 text-sm font-semibold text-background transition-all hover:-translate-y-0.5 hover:bg-gold-soft hover:text-white">Inquire now</a>
                                    @else
                                        <a href="{{ route('login') }}"
                                            class="rounded-full bg-ink px-6 py-3 text-sm font-semibold text-background transition-all hover:-translate-y-0.5 hover:bg-gold-soft hover:text-white">Inquire now</a>
                                    @endauth
                                    <a href="{{ route('halls.show', $hall->id) }}"
                                        class="rounded-full border border-line bg-surface px-6 py-3 text-sm font-semibold text-ink transition-colors hover:border-ink">View details</a>
                                </div>

                                <form id="hall-form-{{ $hall->id }}" action="{{ route('booking.hall.checkout') }}" method="POST"
                                    class="mt-8 hidden space-y-4 border-t border-line/70 pt-6">
                                    @csrf
                                    <input type="hidden" name="hall_id" value="{{ $hall->id }}">
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <label class="block text-[11px] font-semibold uppercase tracking-wider text-stone">Tanggal acara</label>
                                            <input type="date" name="event_date" required
                                                class="mt-1 w-full rounded-lg border border-line bg-background px-3 py-2 text-sm outline-none focus:border-gold" />
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold uppercase tracking-wider text-stone">Sesi waktu</label>
                                            <select name="session_id" required
                                                class="mt-1 w-full rounded-lg border border-line bg-background px-3 py-2 text-sm outline-none focus:border-gold">
                                                @foreach (\App\Models\HallSession::all() as $session)
                                                    <option value="{{ $session->id }}">{{ $session->session_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold uppercase tracking-wider text-stone">Jenis acara</label>
                                        <input type="text" name="event_type" placeholder="Pernikahan / Wisuda / Rapat corporate" required
                                            class="mt-1 w-full rounded-lg border border-line bg-background px-3 py-2 text-sm outline-none focus:border-gold" />
                                    </div>
                                    <button type="submit"
                                        class="w-full rounded-full bg-gold px-6 py-3 text-sm font-semibold text-onyx transition-colors hover:bg-gold-soft hover:text-white">Sewa gedung ini</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <!-- About / Contact -->
    <section id="about" class="mx-auto max-w-container px-5 py-20 md:px-8 md:py-28">
        <div class="grid grid-cols-1 gap-12 lg:grid-cols-2 lg:items-center">
            <div class="relative">
                <div class="overflow-hidden rounded-2xl">
                    <img class="h-[420px] w-full object-cover"
                        src="{{ $coverUrl ?? 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?q=80&w=1170&auto=format&fit=crop' }}"
                        alt="{{ $site['long_name'] }}" />
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
                    <div class="flex items-start gap-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-gold/15 text-gold">
                            <span class="material-symbols-outlined">location_on</span>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-ink">Address</p>
                            <p class="mt-0.5 text-sm text-stone">{{ $site['address'] }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-gold/15 text-gold">
                            <span class="material-symbols-outlined">call</span>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-ink">Reservations</p>
                            <p class="mt-0.5 text-sm text-stone">
                                <a href="tel:{{ $site['phone'] }}" class="hover:text-gold-soft">{{ $site['phone'] }}</a> ·
                                <a href="mailto:{{ $site['email'] }}" class="hover:text-gold-soft">{{ $site['email'] }}</a>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-gold/15 text-gold">
                            <span class="material-symbols-outlined">schedule</span>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-ink">Opening hours</p>
                            <p class="mt-0.5 text-sm text-stone">{{ $site['hours'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection