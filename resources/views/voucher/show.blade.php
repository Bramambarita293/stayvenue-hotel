@extends('layouts.blank')

@section('title', 'E-Voucher – ' . $reservation->reservation_code)

@push('head')
    <style>
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                background: #fff !important;
            }
            #action-bar { display: none; }
            #ticket { box-shadow: none !important; border: 1px solid #D5DCE8 !important; }
        }
        .gold-line {
            background: linear-gradient(90deg, transparent, #1E3A8A 15%, #93B0E8 50%, #1E3A8A 85%, transparent);
        }
    </style>
@endpush

@section('content')
    <div class="flex min-h-screen flex-col items-center justify-center bg-background px-4 py-10 md:py-14">

        <!-- Action bar -->
        <div id="action-bar" class="mb-6 flex w-full max-w-[840px] items-center justify-between">
            <a href="{{ route('user.reservations') }}"
                class="inline-flex items-center gap-1 text-sm font-medium text-ink transition-colors hover:text-navy-dark">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali ke riwayat
            </a>
            <button onclick="window.print()"
                class="inline-flex items-center gap-2 rounded-full bg-ink px-5 py-2.5 text-sm font-semibold text-background transition-colors hover:bg-navy-dark hover:text-white">
                <span class="material-symbols-outlined text-[18px]">print</span> Print voucher
            </button>
        </div>

        <!-- Ticket -->
        <main id="ticket" class="relative w-full max-w-[840px] overflow-hidden rounded-2xl border border-navy/30 bg-surface shadow-card">

            <div class="h-2 w-full gold-line"></div>

            <!-- Header -->
            <header class="relative overflow-hidden bg-onyx px-8 pb-10 pt-12 text-center text-background md:px-16">
                <span class="absolute right-8 top-6 rounded-full border border-success/40 bg-success/10 px-4 py-1.5 text-[11px] font-semibold uppercase tracking-[0.15em] text-background/90">
                    {{ $reservation->status }}
                </span>
                <p class="text-[11px] font-semibold uppercase tracking-[0.35em] text-navy">Official e-voucher</p>
                <h1 class="mt-3 font-display text-4xl font-medium tracking-tight md:text-5xl">
                    {{ $site['name'] }}<span class="text-navy">.</span>
                </h1>
                <div class="mx-auto mt-4 h-px w-24 gold-line"></div>
                <p class="mt-4 text-sm italic text-background/60">Booking confirmation</p>
            </header>

            <!-- Body -->
            <div class="px-8 py-10 md:px-16">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-navy-soft">Primary guest</p>
                        <p class="mt-1 font-display text-lg font-medium text-ink">{{ $reservation->guest_name }}</p>
                        <p class="mt-0.5 text-xs text-muted-text">{{ $reservation->guest_email }} | {{ $reservation->guest_phone }}</p>
                    </div>
                    <div class="md:text-right">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-navy-soft">Booking reference</p>
                        <p class="mt-1 font-mono text-lg font-bold tracking-widest text-ink">{{ $reservation->reservation_code }}</p>
                    </div>
                </div>

                <div class="my-7 h-px w-full gold-line"></div>

                @if ($reservation->reservation_type === 'ROOM' && $reservation->roomBooking)
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-navy-soft">Accommodation</p>
                        <p class="mt-1 font-display text-2xl font-medium text-ink md:text-3xl">
                            {{ $reservation->roomBooking->roomType?->name ?? 'Room' }}
                        </p>
                        <p class="mt-1 text-xs text-muted-text">Jumlah: {{ $reservation->roomBooking->number_of_rooms ?? '-' }} kamar</p>
                    </div>
                    <div class="mt-7 grid grid-cols-2 gap-6">
                        <div>
                            <p class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.2em] text-navy-soft">
                                <span class="material-symbols-outlined text-[18px]">calendar_today</span> Check-in
                            </p>
                            <p class="mt-1.5 font-semibold text-ink">{{ $reservation->roomBooking->check_in_date ? \Carbon\Carbon::parse($reservation->roomBooking->check_in_date)->format('d M Y') : '-' }}</p>
                            <p class="mt-0.5 text-xs text-muted-text">Dari jam 14:00 WIB</p>
                        </div>
                        <div>
                            <p class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.2em] text-navy-soft">
                                <span class="material-symbols-outlined text-[18px]">calendar_month</span> Check-out
                            </p>
                            <p class="mt-1.5 font-semibold text-ink">{{ $reservation->roomBooking->check_out_date ? \Carbon\Carbon::parse($reservation->roomBooking->check_out_date)->format('d M Y') : '-' }}</p>
                            <p class="mt-0.5 text-xs text-muted-text">Sebelum jam 12:00 WIB</p>
                        </div>
                    </div>
                @elseif ($reservation->reservation_type === 'HALL' && $reservation->hallBooking)
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-navy-soft">Venue &amp; event</p>
                        <p class="mt-1 font-display text-2xl font-medium text-ink md:text-3xl">
                            {{ $reservation->hallBooking->hall?->name ?? 'Event Hall' }}
                        </p>
                        <p class="mt-1 text-xs text-muted-text">Jenis acara: {{ $reservation->hallBooking->event_type_label }}</p>
                    </div>
                    <div class="mt-7 grid grid-cols-2 gap-6">
                        <div>
                            <p class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.2em] text-navy-soft">
                                <span class="material-symbols-outlined text-[18px]">event</span> Tanggal acara
                            </p>
                            <p class="mt-1.5 font-semibold text-ink">{{ $reservation->hallBooking->event_date ? \Carbon\Carbon::parse($reservation->hallBooking->event_date)->format('d M Y') : '-' }}</p>
                        </div>
                        <div>
                            <p class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.2em] text-navy-soft">
                                <span class="material-symbols-outlined text-[18px]">schedule</span> Sesi acara
                            </p>
                            <p class="mt-1.5 font-semibold text-ink">{{ $reservation->hallBooking->session?->session_name ?? 'Standard Session' }}</p>
                        </div>
                    </div>
                @endif

                <div class="mt-10">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-navy-soft">Metode pembayaran</p>
                    <p class="mt-1 font-semibold uppercase text-ink">{{ $reservation->payments->first()?->payment_method ?? 'MIDTRANS' }}</p>
                    <p class="mt-0.5 text-xs text-muted-text">
                        Lunas pada {{ $reservation->payments->first()?->paid_at ? \Carbon\Carbon::parse($reservation->payments->first()->paid_at)->format('d M Y  H:i') : '-' }}
                    </p>
                </div>

                <!-- Summary + QR -->
                <div class="mt-8 flex flex-col overflow-hidden rounded-xl bg-onyx text-background md:flex-row">
                    <div class="flex-1 p-7">
                        <h3 class="font-display text-lg font-medium text-navy">Ringkasan pembayaran</h3>
                        <div class="mt-4 flex items-center justify-between border-b border-background/15 pb-3 text-sm">
                            <span class="text-background/70">Total biaya reservasi</span>
                            <span class="font-medium">Rp {{ number_format($reservation->total_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="mt-3 flex items-center justify-between">
                            <span class="text-[11px] font-semibold uppercase tracking-[0.2em] text-background/70">Total lunas</span>
                            <span class="flex items-center gap-1.5 font-display text-xl font-medium text-navy">
                                <span class="material-symbols-outlined text-[18px]">verified</span>
                                Rp {{ number_format($reservation->total_amount, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                    <div class="flex shrink-0 flex-col items-center justify-center border-t border-background/15 p-7 md:w-56 md:border-l md:border-t-0">
                        <div class="mb-3 flex h-24 w-24 items-center justify-center rounded-lg bg-white p-2">
                            <img class="h-full w-full object-contain"
                                src="{{ $qrCode }}"
                                alt="QR Code" />
                        </div>
                        <p class="text-center text-[10px] font-medium uppercase tracking-[0.2em] text-navy">Scan untuk<br />express check-in</p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer class="border-t border-navy/30 bg-muted px-8 py-7 text-center md:px-16">
                <p class="text-sm font-medium text-ink">Tunjukkan e-voucher ini beserta identitas resmi (KTP/Paspor) saat kedatangan.</p>
                <p class="mt-2 text-xs text-muted-text">© {{ date('Y') }} {{ $site['long_name'] }}. All rights reserved.</p>
            </footer>
        </main>
    </div>
@endsection