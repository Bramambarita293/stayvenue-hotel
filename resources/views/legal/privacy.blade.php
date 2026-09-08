@extends('layouts.app')

@section('title', 'Privacy Policy')

@section('content')
    <section class="bg-onyx pb-16 pt-36 text-background">
        <div class="mx-auto max-w-container px-5 md:px-8">
            <p class="eyebrow text-gold">Legal</p>
            <h1 class="mt-4 font-display text-5xl font-medium tracking-tight md:text-6xl">Privacy Policy</h1>
            <p class="mt-5 max-w-2xl text-base font-light leading-relaxed text-background/70">
                Terakhir diperbarui: {{ date('d M Y') }}. Kebijakan ini menjelaskan data yang kami kumpulkan dan cara kami menggunakannya.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-container px-5 py-16 md:px-8 md:py-20">
        <div class="max-w-3xl space-y-8 text-sm leading-relaxed text-stone">
            <div>
                <h2 class="font-display text-2xl font-medium text-ink">1. Data yang kami kumpulkan</h2>
                <p class="mt-3">Saat membuat akun dan memesan, kami mengumpulkan nama, email, nomor telepon, detail reservasi (tanggal, tipe kamar/gedung, permintaan khusus), serta status pembayaran dari Midtrans. Kami tidak menyimpan nomor kartu atau kredensial e-wallet — seluruh pembayaran diproses aman oleh Midtrans.</p>
            </div>
            <div>
                <h2 class="font-display text-2xl font-medium text-ink">2. Penggunaan data</h2>
                <p class="mt-3">Data digunakan untuk memproses reservasi, menerbitkan e-voucher, menghubungi Anda terkait pesanan, mencegah penipuan, dan memenuhi kewajiban hukum. Kami tidak menjual data pribadi Anda.</p>
            </div>
            <div>
                <h2 class="font-display text-2xl font-medium text-ink">3. Penyimpanan & keamanan</h2>
                <p class="mt-3">Password disimpan dalam bentuk hash. Akses panel admin dibatasi untuk staf berwenang. Foto galeri disimpan di penyimpanan cloud dengan akses baca publik.</p>
            </div>
            <div>
                <h2 class="font-display text-2xl font-medium text-ink">4. Hak Anda</h2>
                <p class="mt-3">Anda dapat meminta akses, koreksi, atau penghapusan data pribadi melalui halaman <a href="{{ route('contact') }}" class="font-semibold text-gold-soft underline underline-offset-4">kontak</a> atau email {{ $site['email'] }}.</p>
            </div>
            <div>
                <h2 class="font-display text-2xl font-medium text-ink">5. Perubahan kebijakan</h2>
                <p class="mt-3">Perubahan akan diumumkan di halaman ini dengan tanggal pembaruan terbaru.</p>
            </div>
        </div>
    </section>
@endsection
