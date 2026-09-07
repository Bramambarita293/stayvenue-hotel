{{-- Strip promo berjalan (marquee). Sembunyi otomatis bila tidak ada promo aktif. --}}
@php
    $activePromos = collect($site['promos'] ?? [])->filter(fn ($p) => empty($p['valid_until']) || \Carbon\Carbon::parse($p['valid_until'])->endOfDay()->gte(now()))->values();
@endphp

@if ($activePromos->isNotEmpty())
    <div class="overflow-hidden border-y border-line/70 bg-onyx py-3" aria-label="Promosi berjalan">
        <div class="promo-marquee flex w-max items-center gap-10 whitespace-nowrap text-sm text-background/90">
            @foreach ($activePromos->merge($activePromos)->take($activePromos->count() * 2) as $promo)
                <span class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-[16px] text-gold">verified</span>
                    <span class="font-semibold">{{ $promo['title'] }}</span>
                    <span class="text-background/50">·</span>
                    <span class="text-background/70">{{ $promo['badge'] }}</span>
                </span>
            @endforeach
        </div>
    </div>
@endif
