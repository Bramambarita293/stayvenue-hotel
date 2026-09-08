@extends('layouts.app')

@section('title', 'Terms of Service')

@section('content')
    <section class="bg-onyx pb-16 pt-36 text-background">
        <div class="mx-auto max-w-container px-5 md:px-8">
            <p class="eyebrow text-gold">Legal</p>
            <h1 class="mt-4 font-display text-5xl font-medium tracking-tight md:text-6xl">Terms of Service</h1>
            <p class="mt-5 max-w-2xl text-base font-light leading-relaxed text-background/70">
                Terakhir diperbarui: {{ date('d M Y') }}. Dengan memesan di {{ $site['long_name'] }}, Anda menyetujui ketentuan berikut.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-container px-5 py-16 md:px-8 md:py-20">
        <div class="max-w-3xl space-y-8 text-sm leading-relaxed text-stone">
            <div>
                <h2 class="font-display text-2xl font-medium text-ink">1. Reservasi & pembayaran</h2>
                <p class="mt-3">Reservasi terkunci setelah pembayaran terkonfirmasi melalui Midtrans dan e-voucher diterbitkan. Pesanan yang belum dibayar hangus otomatis setelah 24 jam tanpa biaya.</p>
            </div>
            <div>
                <h2 class="font-display text-2xl font-medium text-ink">2. Check-in & check-out</h2>
                <p class="mt-3">Check-in mulai pukul 14:00 WIB dengan menunjukkan e-voucher/identitas; check-out sebelum pukul 12:00 WIB. Keterlambatan dapat dikenakan biaya tambahan sesuai kebijakan front desk.</p>
            </div>
            <div>
                <h2 class="font-display text-2xl font-medium text-ink">3. Pembatalan</h2>
                <p class="mt-3">Pembatalan gratis hingga 48 jam sebelum check-in. Untuk pesanan lunas, pengembalian dana mengikuti kebijakan rate plan — hubungi {{ $site['phone'] }} atau {{ $site['email'] }}.</p>
            </div>
            <div>
                <h2 class="font-display text-2xl font-medium text-ink">4. Sewa gedung</h2>
                <p class="mt-3">Penyewa bertanggung jawab atas tamu dan vendor selama acara, termasuk setup dan pembersihan area sesuai kesepakatan dengan tim event kami.</p>
            </div>
            <div>
                <h2 class="font-display text-2xl font-medium text-ink">5. Batasan tanggung jawab</h2>
                <p class="mt-3">Layanan disediakan sebagaimana adanya. Tanggung jawab kami maksimal sebesar nilai transaksi terkait, kecuali diwajibkan lain oleh hukum yang berlaku.</p>
            </div>
        </div>
    </section>
@endsection
