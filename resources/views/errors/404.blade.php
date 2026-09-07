@extends('layouts.app')

@section('title', 'Tidak tersedia')

@section('content')
    <section class="mx-auto max-w-container px-5 py-28 text-center md:px-8">
        <span class="material-symbols-outlined text-6xl text-stone/40">search_off</span>
        <h1 class="mt-4 font-display text-4xl text-ink">Kamar atau venue tidak tersedia</h1>
        <p class="mx-auto mt-3 max-w-md text-sm text-stone">
            Halaman yang Anda cari tidak ada, sudah penuh, atau telah dinonaktifkan pengelola.
        </p>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ route('rooms.index') }}"
                class="rounded-full bg-ink px-6 py-3 text-sm font-semibold text-background transition-colors hover:bg-gold-soft hover:text-white">Lihat kamar lain</a>
            <a href="{{ route('halls.index') }}"
                class="rounded-full border border-line bg-surface px-6 py-3 text-sm font-semibold text-ink transition-colors hover:border-ink">Lihat gedung lain</a>
        </div>
    </section>
@endsection
