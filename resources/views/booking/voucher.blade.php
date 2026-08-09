<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Stay Venue - E-Voucher</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet"/>

    <script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            "colors": {
              "outline": "#74777f",
              "primary": "#000613",
              "surface-dim": "#e1d8d4",
              "surface-container-lowest": "#ffffff",
              "secondary-fixed": "#e9c349",
              "gold": "#c9a24b",
              "gold-dark": "#9c7c2f",
              "background": "#fff8f5",
              "primary-container": "#001f3f",
              "primary-deep": "#000613",
              "on-primary-container": "#6f88ad",
              "on-surface-variant": "#43474e",
              "on-surface": "#1e1b18"
            },
            "fontFamily": {
              "display": ["Playfair Display", "serif"],
              "body": ["Montserrat", "sans-serif"]
            }
          }
        }
      }
    </script>
    <style>
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                background: #fff !important;
            }
            #print-button { display: none; }
            main { box-shadow: none !important; }
        }

        .voucher-bg-pattern {
            background-image:
                radial-gradient(circle at 1px 1px, rgba(201,162,75,0.35) 1px, transparent 0);
            background-size: 18px 18px;
        }

        .gold-line {
            background: linear-gradient(90deg, transparent, #e9c349 15%, #f6e6ab 50%, #e9c349 85%, transparent);
        }

        .corner-flourish {
            border-color: #c9a24b;
        }

        /* Ticket tear / perforation effect between main info and QR stub */
        .ticket-seam {
            position: relative;
        }
        .ticket-seam::before,
        .ticket-seam::after {
            content: "";
            position: absolute;
            top: -14px;
            width: 28px;
            height: 28px;
            background: #e1d8d4;
            border-radius: 9999px;
            z-index: 10;
        }
        .ticket-seam::before { left: -14px; }
        .ticket-seam::after { right: -14px; }

        .dashed-divider {
            background-image: repeating-linear-gradient(90deg, #c9a24b 0 10px, transparent 10px 18px);
            height: 2px;
        }

        .seal {
            background: radial-gradient(circle at 30% 30%, #f6e6ab, #c9a24b 55%, #9c7c2f 100%);
        }
    </style>
</head>
<body class="bg-surface-dim min-h-screen flex flex-col items-center justify-center py-8 md:py-14 px-4 font-body text-on-surface">

<!-- Action Bar (Hidden on Print) -->
<div class="w-full max-w-[820px] flex justify-between items-center mb-6" id="print-button">
    <a href="{{ route('user.reservations') }}" class="text-sm font-semibold text-primary hover:underline flex items-center gap-1">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali ke Riwayat
    </a>
    <button onclick="window.print()" class="bg-primary-container text-white text-sm font-semibold py-2.5 px-5 rounded flex items-center gap-2 hover:bg-primary transition-colors shadow-sm">
        <span class="material-symbols-outlined text-[20px]">print</span> Print Voucher
    </button>
</div>

<!-- E-Voucher Container -->
<main class="bg-background w-full max-w-[820px] shadow-[0_25px_60px_rgba(0,6,19,0.18)] relative overflow-hidden flex flex-col rounded-md border border-gold/30">

    <!-- Top Gold Accent -->
    <div class="h-2.5 w-full gold-line"></div>

    <!-- Header -->
    <header class="pt-14 pb-10 px-8 md:px-16 text-center relative bg-primary-deep text-background overflow-hidden">
        <!-- subtle dotted pattern overlay -->
        <div class="absolute inset-0 voucher-bg-pattern opacity-20 pointer-events-none"></div>

        <!-- Status Seal -->
        <div class="absolute top-6 right-8 md:right-16">
            <span class="bg-emerald-400/10 text-emerald-300 font-semibold text-[11px] px-4 py-1.5 rounded-full border border-emerald-400/40 tracking-[0.15em] uppercase">
                {{ $reservation->status }}
            </span>
        </div>

        <div class="relative z-10">
            <span class="block text-gold text-[11px] tracking-[0.35em] uppercase mb-3">Official E-Voucher</span>
            <h1 class="font-display text-4xl md:text-6xl font-bold tracking-tight mb-2">
                <span class="text-secondary-fixed">Stay</span> <span class="text-background">Venue</span>
            </h1>
            <div class="w-24 h-px gold-line mx-auto my-4"></div>
            <h2 class="font-display text-base md:text-lg text-background/70 italic tracking-wide">Booking Confirmation</h2>
        </div>
    </header>

    <!-- Body Content -->
    <div class="px-8 md:px-16 py-10 flex-grow flex flex-col gap-8 relative">

        <!-- Guest & Booking Info Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-[11px] text-gold-dark uppercase tracking-[0.2em] mb-1 font-semibold">Primary Guest</p>
                <p class="text-lg font-display font-semibold text-primary">{{ $reservation->guest_name }}</p>
                <p class="text-xs text-outline">{{ $reservation->guest_email }} | {{ $reservation->guest_phone }}</p>
            </div>
            <div class="md:text-right">
                <p class="text-[11px] text-gold-dark uppercase tracking-[0.2em] mb-1 font-semibold">Booking Reference</p>
                <p class="text-lg font-bold text-primary font-mono tracking-widest">{{ $reservation->reservation_code }}</p>
            </div>
        </div>

        <div class="h-px w-full gold-line"></div>

        <!-- Stay / Event Details Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-8 gap-x-6">

            @if($reservation->reservation_type === 'ROOM' && $reservation->roomBooking)
                <!-- ROOM BOOKING DETAILS -->
                <div class="col-span-1 md:col-span-2">
                    <p class="text-[11px] text-gold-dark uppercase tracking-[0.2em] mb-1 font-semibold">Accommodation</p>
                    <p class="font-display text-2xl md:text-3xl font-bold text-primary">
                        {{ $reservation->roomBooking->roomType->name ?? 'Room' }}
                    </p>
                    <p class="text-xs text-outline mt-1">Jumlah: {{ $reservation->roomBooking->number_of_rooms }} Kamar</p>
                </div>

                <div>
                    <div class="flex items-center gap-2 mb-1 text-gold-dark">
                        <span class="material-symbols-outlined text-[18px]">calendar_today</span>
                        <p class="text-[11px] uppercase tracking-[0.2em] font-semibold">Check-in</p>
                    </div>
                    <p class="text-base font-semibold text-primary">
                        {{ \Carbon\Carbon::parse($reservation->roomBooking->check_in_date)->format('d M Y') }}
                    </p>
                    <p class="text-xs text-outline mt-0.5">Dari jam 14:00 WIB</p>
                </div>

                <div>
                    <div class="flex items-center gap-2 mb-1 text-gold-dark">
                        <span class="material-symbols-outlined text-[18px]">calendar_month</span>
                        <p class="text-[11px] uppercase tracking-[0.2em] font-semibold">Check-out</p>
                    </div>
                    <p class="text-base font-semibold text-primary">
                        {{ \Carbon\Carbon::parse($reservation->roomBooking->check_out_date)->format('d M Y') }}
                    </p>
                    <p class="text-xs text-outline mt-0.5">Sebelum jam 12:00 WIB</p>
                </div>

            @elseif($reservation->reservation_type === 'HALL' && $reservation->hallBooking)
                <!-- HALL BOOKING DETAILS -->
                <div class="col-span-1 md:col-span-2">
                    <p class="text-[11px] text-gold-dark uppercase tracking-[0.2em] mb-1 font-semibold">Venue & Event</p>
                    <p class="font-display text-2xl md:text-3xl font-bold text-primary">
                        {{ $reservation->hallBooking->hall->name ?? 'Event Hall' }}
                    </p>
                    <p class="text-xs text-outline mt-1">Jenis Acara: {{ $reservation->hallBooking->event_type }}</p>
                </div>

                <div>
                    <div class="flex items-center gap-2 mb-1 text-gold-dark">
                        <span class="material-symbols-outlined text-[18px]">event</span>
                        <p class="text-[11px] uppercase tracking-[0.2em] font-semibold">Tanggal Acara</p>
                    </div>
                    <p class="text-base font-semibold text-primary">
                        {{ \Carbon\Carbon::parse($reservation->hallBooking->event_date)->format('d M Y') }}
                    </p>
                </div>

                <div>
                    <div class="flex items-center gap-2 mb-1 text-gold-dark">
                        <span class="material-symbols-outlined text-[18px]">schedule</span>
                        <p class="text-[11px] uppercase tracking-[0.2em] font-semibold">Sesi Acara</p>
                    </div>
                    <p class="text-base font-semibold text-primary">
                        {{ $reservation->hallBooking->session->name ?? 'Standard Session' }}
                    </p>
                </div>
            @endif

            <div>
                <div class="flex items-center gap-2 mb-1 text-gold-dark">
                    <span class="material-symbols-outlined text-[18px]">payments</span>
                    <p class="text-[11px] uppercase tracking-[0.2em] font-semibold">Metode Pembayaran</p>
                </div>
                <p class="text-base font-semibold text-primary uppercase">
                    {{ $reservation->payments->first()->payment_method ?? 'MIDTRANS' }}
                </p>
                <p class="text-xs text-outline mt-0.5">
                    Lunas pada {{ $reservation->payments->first()->paid_at ? \Carbon\Carbon::parse($reservation->payments->first()->paid_at)->format('d M Y H:i') : '-' }}
                </p>
            </div>
        </div>

        <!-- Payment Summary & QR Code (Ticket Stub Style) -->
        <div class="ticket-seam bg-primary-deep rounded-md flex flex-col md:flex-row shadow-inner overflow-hidden">
            <!-- Summary Side -->
            <div class="flex-grow w-full p-6 md:p-8 text-background">
                <h3 class="font-display text-lg font-bold text-secondary-fixed mb-4 tracking-wide">Ringkasan Pembayaran</h3>

                <div class="flex justify-between items-center py-2 border-b border-background/15 text-sm">
                    <span class="text-background/70">Total Biaya Reservasi</span>
                    <span class="text-background font-medium">Rp {{ number_format($reservation->total_amount, 0, ',', '.') }}</span>
                </div>

                <div class="flex justify-between items-center py-3 mt-1">
                    <span class="text-[11px] font-bold text-background/70 uppercase tracking-[0.2em]">Total Lunas</span>
                    <span class="font-display text-xl md:text-2xl font-bold text-secondary-fixed flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-emerald-400 text-[20px]">verified</span>
                        Rp {{ number_format($reservation->total_amount, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <!-- Dashed vertical divider (desktop) -->
            <div class="hidden md:block w-px my-6 dashed-divider" style="width:2px; height:auto; background-image: repeating-linear-gradient(180deg, #c9a24b 0 10px, transparent 10px 18px);"></div>
            <!-- Dashed horizontal divider (mobile) -->
            <div class="md:hidden dashed-divider mx-6"></div>

            <!-- QR Code Side -->
            <div class="flex flex-col items-center justify-center shrink-0 p-6 md:p-8 md:w-56">
                <div class="w-28 h-28 bg-white p-2 rounded mb-3 flex items-center justify-center shadow-md">
                    <img class="w-full h-full object-contain"
                         src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($reservation->reservation_code) }}"
                         alt="QR Code Kode Pemesanan"/>
                </div>
                <p class="text-[10px] text-secondary-fixed text-center uppercase tracking-[0.2em] font-medium">
                    Scan for<br/>Express Check-in
                </p>
            </div>
        </div>

    </div>

    <!-- Bottom Gold Accent -->
    <div class="h-1 w-full gold-line"></div>

    <!-- Footer -->
    <footer class="bg-primary-deep w-full mt-auto flex flex-col md:flex-row justify-between items-center px-8 md:px-16 py-8 text-background gap-4 relative overflow-hidden">
        <div class="absolute inset-0 voucher-bg-pattern opacity-10 pointer-events-none"></div>
        <div class="w-full mx-auto flex flex-col md:flex-row justify-between items-center gap-6 relative z-10">
            <div class="flex flex-col items-center md:items-start text-center md:text-left gap-2">
                <span class="font-display text-xl font-bold">
                    <span class="text-secondary-fixed">Stay</span> <span class="text-background">Venue</span>
                </span>
                <p class="text-xs text-background/70 max-w-md">
                    Tunjukkan E-Voucher ini beserta kartu identitas resmi (KTP/Passport) yang sesuai dengan nama pemesan saat kedatangan.
                </p>
            </div>
            <div class="flex flex-col items-center md:items-end gap-2 w-full md:w-auto">
                <p class="text-[11px] text-background/50">© {{ date('Y') }} Stay Venue Luxury Hospitality Group. All rights reserved.</p>
            </div>
        </div>
    </footer>
</main>

</body>
</html>