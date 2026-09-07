{{-- Status ketersediaan inline (diisi JS real-time). Fallback server: hidden. --}}
@props(['ctaUrl' => null, 'ctaLabel' => 'Lihat kamar lain'])

<div {{ $attributes->merge(['class' => 'availability-status mt-3 hidden rounded-xl border p-3 text-sm', 'role' => 'status', 'aria-live' => 'polite']) }}
    data-availability-status>
    <p class="font-semibold" data-availability-message></p>
    @if ($ctaUrl)
        <a href="{{ $ctaUrl }}" class="mt-1 inline-block text-xs font-semibold text-gold-soft underline underline-offset-4">{{ $ctaLabel }}</a>
    @endif
</div>
