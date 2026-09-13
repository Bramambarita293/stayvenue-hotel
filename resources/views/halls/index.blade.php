@extends('layouts.app')

@section('title', 'Event Venues')

@section('content')
    <section class="bg-onyx pb-16 pt-36 text-background">
        <div class="mx-auto max-w-container px-5 md:px-8">
            <p class="eyebrow text-navy">Unforgettable gatherings</p>
            <h1 class="mt-4 font-display text-5xl font-medium tracking-tight md:text-6xl">Event Venues</h1>
            <p class="mt-5 max-w-2xl text-base font-light leading-relaxed text-background/70">
                From executive meetings to grand weddings, our ballrooms are composed to stage
                the moments that matter most.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-container px-5 py-16 md:px-8 md:py-20">
        @if ($halls->isEmpty())
            <div class="rounded-2xl border border-border/70 bg-surface py-20 text-center">
                <span class="material-symbols-outlined text-5xl text-muted-text/40">meeting_room</span>
                <p class="mt-4 font-display text-2xl text-ink">Belum ada gedung yang tersedia</p>
                <p class="mt-2 text-sm text-muted-text">Silakan cek kembali nanti.</p>
            </div>
        @else
            <div class="space-y-14">
                @foreach ($halls as $hall)
                    @php
                        $hallImages = is_string($hall->images) ? json_decode($hall->images, true) : $hall->images;
                        $firstImage = \App\Support\HotelImage::url((!empty($hallImages) && is_array($hallImages) && count($hallImages) > 0) ? $hallImages[0] : null, 'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?q=80&w=1170&auto=format&fit=crop');
                        $reverse = $loop->iteration % 2 === 0;
                    @endphp
                    <div class="flex flex-col gap-8 overflow-hidden rounded-2xl border border-border/70 bg-surface shadow-card md:flex-row {{ $reverse ? 'md:flex-row-reverse' : '' }}">
                        <div class="relative h-64 w-full overflow-hidden md:h-auto md:w-1/2">
                            <img class="h-full w-full object-cover transition-transform duration-700 hover:scale-105"
                                src="{{ $firstImage }}" alt="{{ $hall->name }}" loading="lazy" decoding="async" />
                        </div>

                        <div class="flex flex-1 flex-col justify-center p-7 md:p-10">
                            <p class="eyebrow">{{ $reverse ? 'Intimate elegance' : 'Grand scale' }}</p>
                            <h2 class="mt-3 font-display text-3xl font-medium text-ink md:text-4xl">{{ $hall->name }}</h2>
                            <p class="mt-4 text-sm leading-relaxed text-muted-text">{{ $hall->description }}</p>

                            <div class="mt-6 flex flex-wrap gap-x-10 gap-y-3">
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-muted-text">Capacity</p>
                                    <p class="mt-1 font-display text-xl text-ink">Up to {{ $hall->capacity_pax }} pax</p>
                                </div>
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-muted-text">Base rental</p>
                                    <p class="mt-1 font-display text-xl text-ink">
                                        Rp {{ number_format($hall->base_rental_price, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>

                            <a href="{{ route('halls.show', $hall->id) }}"
                                class="mt-8 inline-flex w-fit items-center gap-2 rounded-full bg-ink px-6 py-3 text-sm font-semibold text-background transition-all hover:-translate-y-0.5 hover:bg-navy-dark hover:text-white">
                                View venue <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
@endsection