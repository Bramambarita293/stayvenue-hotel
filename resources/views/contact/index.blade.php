@extends('layouts.app')

@section('title', 'Contact & Lokasi')

@section('content')
    <!-- Hero -->
    <section class="relative overflow-hidden bg-onyx pb-16 pt-36 text-background">
        <div class="mx-auto max-w-container px-5 md:px-8">
            <p class="eyebrow text-navy">We are here for you</p>
            <h1 class="mt-4 font-display text-5xl font-medium tracking-tight md:text-6xl">Contact &amp; Location</h1>
            <p class="mt-5 max-w-2xl text-base font-light leading-relaxed text-background/70">
                Ada pertanyaan seputar menginap atau acara Anda? Tim kami siap membantu 24 jam.
            </p>
        </div>
    </section>

    <!-- Notifications -->
    @if (session('success'))
        <div class="mx-auto mt-8 max-w-container px-5 md:px-8">
            <div class="flex items-start gap-3 rounded-xl border border-success/30 bg-success/10 p-4 text-sm text-success">
                <span class="material-symbols-outlined text-[20px]">check_circle</span>
                <p class="font-semibold">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="mx-auto mt-8 max-w-container px-5 md:px-8">
            <div class="flex items-start gap-3 rounded-xl border border-danger/20 bg-danger/5 p-4 text-sm text-danger">
                <span class="material-symbols-outlined text-[20px]">error</span>
                <div>
                    <p class="font-semibold">Pesan belum terkirim</p>
                    <ul class="mt-1 list-disc space-y-0.5 pl-5 opacity-90">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Info kontak + form -->
    <section class="mx-auto max-w-container px-5 py-16 md:px-8 md:py-24">
        <div class="grid grid-cols-1 gap-12 lg:grid-cols-5">
            <!-- Info kontak -->
            <div class="lg:col-span-2">
                <p class="eyebrow">Get in touch</p>
                <h2 class="mt-3 font-display text-3xl font-medium tracking-tight text-ink">Hubungi kami langsung</h2>

                <div class="mt-8 space-y-6">
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined mt-0.5 text-[20px] text-navy">location_on</span>
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-wider text-ink">Alamat</p>
                            <p class="mt-1 text-sm leading-relaxed text-muted-text">{{ $site['address'] }}</p>
                            <a href="{{ $site['map_link'] }}" target="_blank" rel="noopener"
                                class="mt-1 inline-flex items-center gap-1 text-sm font-medium text-navy-soft hover:underline">
                                Buka di Google Maps
                                <span class="material-symbols-outlined text-[14px]">open_in_new</span>
                            </a>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined mt-0.5 text-[20px] text-navy">call</span>
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-wider text-ink">Telepon &amp; WhatsApp</p>
                            <p class="mt-1 text-sm text-muted-text">
                                <a href="tel:{{ preg_replace('/[^+\d]/', '', $site['phone']) }}"
                                    class="hover:text-navy-soft">{{ $site['phone'] }}</a>
                            </p>
                            <a href="https://wa.me/{{ $site['whatsapp'] }}?text={{ urlencode('Halo Admin StayVenue!, saya ingin bertanya tentang...') }}"
                                target="_blank" rel="noopener"
                                class="mt-1 inline-flex items-center gap-1 text-sm font-medium text-navy-soft hover:underline">
                                Chat via WhatsApp
                                <span class="material-symbols-outlined text-[14px]">open_in_new</span>
                            </a>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined mt-0.5 text-[20px] text-navy">mail</span>
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-wider text-ink">Email</p>
                            <p class="mt-1 text-sm text-muted-text">
                                <a href="mailto:{{ $site['email'] }}" class="hover:text-navy-soft">{{ $site['email'] }}</a>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined mt-0.5 text-[20px] text-navy">schedule</span>
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-wider text-ink">Jam Operasional</p>
                            <p class="mt-1 text-sm text-muted-text">{{ $site['hours'] }} · Restoran 06:00 – 23:00 WIB</p>
                        </div>
                    </div>
                </div>

                <!-- Sosial media -->
                <div class="mt-10 border-t border-border/70 pt-8">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-navy">Ikuti kami</p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        @if (!empty($site['socials']['instagram']))
                            <a href="{{ $site['socials']['instagram'] }}" target="_blank" rel="noopener"
                                class="inline-flex items-center gap-2 rounded-full border border-border bg-surface px-4 py-2 text-sm font-medium text-ink transition-colors hover:border-ink">
                                Instagram
                            </a>
                        @endif
                        @if (!empty($site['socials']['facebook']))
                            <a href="{{ $site['socials']['facebook'] }}" target="_blank" rel="noopener"
                                class="inline-flex items-center gap-2 rounded-full border border-border bg-surface px-4 py-2 text-sm font-medium text-ink transition-colors hover:border-ink">
                                Facebook
                            </a>
                        @endif
                        @if (!empty($site['socials']['tiktok']))
                            <a href="{{ $site['socials']['tiktok'] }}" target="_blank" rel="noopener"
                                class="inline-flex items-center gap-2 rounded-full border border-border bg-surface px-4 py-2 text-sm font-medium text-ink transition-colors hover:border-ink">
                                TikTok
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Form pesan -->
            <div class="rounded-2xl border border-border/70 bg-surface p-7 shadow-card lg:col-span-3 md:p-10">
                <h3 class="font-display text-2xl font-medium tracking-tight text-ink">Kirim pesan</h3>
                <p class="mt-2 text-sm text-muted-text">Isi formulir di bawah — kami biasanya membalas dalam beberapa jam kerja.</p>

                <form action="{{ route('contact.store') }}" method="POST" class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2">
                    @csrf
                    <div>
                        <label for="name" class="block text-[11px] font-semibold uppercase tracking-wider text-muted-text">Nama lengkap *</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required maxlength="100"
                            placeholder="Nama Anda"
                            class="mt-1.5 w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none transition-colors focus:border-navy focus:ring-1 focus:ring-navy/30" />
                    </div>
                    <div>
                        <label for="email" class="block text-[11px] font-semibold uppercase tracking-wider text-muted-text">Email *</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required maxlength="150"
                            placeholder="nama@email.com"
                            class="mt-1.5 w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none transition-colors focus:border-navy focus:ring-1 focus:ring-navy/30" />
                    </div>
                    <div>
                        <label for="phone" class="block text-[11px] font-semibold uppercase tracking-wider text-muted-text">No. Telepon / WA</label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" maxlength="25"
                            placeholder="+62 8xx xxxx xxxx"
                            class="mt-1.5 w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none transition-colors focus:border-navy focus:ring-1 focus:ring-navy/30" />
                    </div>
                    <div>
                        <label for="subject" class="block text-[11px] font-semibold uppercase tracking-wider text-muted-text">Subjek</label>
                        <select id="subject" name="subject"
                            class="mt-1.5 w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none transition-colors focus:border-navy focus:ring-1 focus:ring-navy/30">
                            <option value="">Pilih subjek (opsional)</option>
                            @foreach (['Reservasi kamar', 'Sewa gedung / acara', 'Kerjasama & event', 'Feedback', 'Lainnya'] as $opt)
                                <option value="{{ $opt }}" {{ old('subject') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="message" class="block text-[11px] font-semibold uppercase tracking-wider text-muted-text">Pesan Anda *</label>
                        <textarea id="message" name="message" rows="6" required minlength="10" maxlength="2000"
                            placeholder="Ceritakan kebutuhan Anda — tanggal, jumlah tamu, jenis acara, atau pertanyaan lainnya."
                            class="mt-1.5 w-full resize-y rounded-lg border border-border bg-background px-4 py-3 text-sm outline-none transition-colors focus:border-navy focus:ring-1 focus:ring-navy/30">{{ old('message') }}</textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <button type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-ink px-8 py-3.5 text-sm font-semibold text-background transition-all hover:-translate-y-0.5 hover:bg-navy-dark hover:text-white sm:w-auto">
                            Kirim Pesan
                            <span class="material-symbols-outlined text-[18px]">send</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Peta lokasi -->
    <section class="border-y border-border/70 bg-muted">
        <div class="mx-auto max-w-container px-5 py-16 md:px-8 md:py-20">
            <div class="mb-10 flex flex-col justify-between gap-4 md:flex-row md:items-end">
                <div>
                    <p class="eyebrow">Find us</p>
                    <h2 class="mt-3 font-display text-4xl font-medium tracking-tight text-ink md:text-5xl">Lokasi Hotel</h2>
                    <p class="mt-3 max-w-xl text-sm leading-relaxed text-muted-text">
                        Di jantung Menteng, Jakarta Pusat — 10 menit dari Monas, 45 menit dari Soekarno-Hatta International Airport.
                    </p>
                </div>
                <a href="{{ $site['map_link'] }}" target="_blank" rel="noopener"
                    class="group inline-flex items-center gap-1 text-sm font-semibold uppercase tracking-widest text-ink">
                    Petunjuk arah
                    <span class="material-symbols-outlined text-[18px] transition-transform group-hover:translate-x-1">arrow_forward</span>
                </a>
            </div>
            <div class="overflow-hidden rounded-2xl border border-border/70 shadow-card">
                <iframe src="{{ $site['map_embed'] }}" width="100%" height="420" style="border:0;" allowfullscreen
                    loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                    title="Peta lokasi {{ $site['long_name'] }}"></iframe>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="mx-auto max-w-container px-5 py-16 md:px-8 md:py-24">
        <div class="mx-auto max-w-3xl">
            <div class="text-center">
                <p class="eyebrow justify-center">FAQ</p>
                <h2 class="mt-3 font-display text-4xl font-medium tracking-tight text-ink md:text-5xl">Pertanyaan Umum</h2>
            </div>
            <div class="mt-10 space-y-4">
                @forelse ($site['faqs'] as $faq)
                    <details class="group rounded-xl border border-border/70 bg-surface transition-shadow open:shadow-card">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-6 py-5 [&::-webkit-details-marker]:hidden">
                            <span class="text-sm font-semibold text-ink md:text-base">{{ $faq['q'] }}</span>
                            <span class="material-symbols-outlined shrink-0 text-muted-text transition-transform duration-300 group-open:rotate-180 group-open:text-navy">expand_more</span>
                        </summary>
                        <p class="px-6 pb-6 text-sm leading-relaxed text-muted-text">{{ $faq['a'] }}</p>
                    </details>
                @empty
                    {{-- FAQ belum diisi di config/site.php --}}
                @endforelse
            </div>
        </div>
    </section>
@endsection
