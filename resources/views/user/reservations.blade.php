@extends('layouts.app')

@section('title', 'My Reservations')

@section('content')
    <section class="mx-auto max-w-container px-5 py-28 md:px-8">
        <div class="mb-8">
            <p class="eyebrow">Your account</p>
            <h1 class="mt-3 font-display text-4xl font-medium tracking-tight text-ink md:text-5xl">My Reservations</h1>
            <p class="mt-3 text-sm text-muted-text">Kelola transaksi, pembayaran, dan unduh E-Voucher resmi Anda.</p>
        </div>

        <div class="overflow-hidden rounded-2xl border border-border/70 bg-surface shadow-card">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-border/70 bg-muted text-xs uppercase tracking-wider text-muted-text">
                            <th class="p-5 font-semibold">Kode Booking</th>
                            <th class="p-5 font-semibold">Kategori &amp; Detail</th>
                            <th class="p-5 font-semibold">Total Biaya</th>
                            <th class="p-5 font-semibold">Status</th>
                            <th class="p-5 text-center font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line/70 text-sm">
                        @forelse ($reservations as $res)
                            <tr class="transition-colors hover:bg-background/60">
                                <td class="p-5">
                                    <span class="font-mono text-sm font-bold text-ink">{{ $res->reservation_code }}</span>
                                    <div class="mt-1 text-xs text-muted-text">{{ $res->created_at->format('d M Y  H:i') }}</div>
                                </td>

                                <td class="p-5">
                                    @if ($res->reservation_type == 'ROOM')
                                        <span class="inline-flex rounded-full bg-navy/15 px-3 py-1 text-[11px] font-semibold uppercase tracking-wider text-navy-dark">
                                            Kamar Hotel
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-muted px-3 py-1 text-[11px] font-semibold uppercase tracking-wider text-muted-text">
                                            Sewa Gedung
                                        </span>
                                    @endif

                                    @if ($res->reservation_type == 'ROOM' && $res->roomBooking)
                                        <div class="mt-2 text-xs text-muted-text">
                                            <span class="font-semibold text-ink">{{ $res->roomBooking->roomType?->name ?? '-' }}</span>
                                            ({{ $res->roomBooking->number_of_rooms }} unit)<br />
                                            Check-in {{ \Carbon\Carbon::parse($res->roomBooking->check_in_date)->format('d M Y') }}
                                        </div>
                                    @elseif ($res->reservation_type == 'HALL' && $res->hallBooking)
                                        <div class="mt-2 text-xs text-muted-text">
                                            <span class="font-semibold text-ink">{{ $res->hallBooking->hall?->name ?? '-' }}</span>
                                            – {{ $res->hallBooking->event_type_label }}<br />
                                            Tanggal {{ \Carbon\Carbon::parse($res->hallBooking->event_date)->format('d M Y') }}
                                        </div>
                                    @endif
                                </td>

                                <td class="p-5 font-semibold text-ink">
                                    Rp {{ number_format($res->total_amount, 0, ',', '.') }}
                                </td>

                                <td class="p-5">
                                    @if (in_array($res->status, ['CONFIRMED', 'COMPLETED', 'CHECKED_IN', 'CHECKED_OUT']))
                                        <span class="inline-flex items-center gap-1 rounded-full bg-success/10 px-3 py-1 text-xs font-semibold text-success">
                                            <span class="material-symbols-outlined text-[14px]">check_circle</span> LUNAS
                                        </span>
                                    @elseif ($res->status == 'PENDING_PAYMENT')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-600">
                                            <span class="material-symbols-outlined text-[14px]">schedule</span> MENUNGGU
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-danger/10 px-3 py-1 text-xs font-semibold text-danger">
                                            <span class="material-symbols-outlined text-[14px]">cancel</span> {{ $res->status }}
                                        </span>
                                    @endif
                                </td>

                                <td class="p-5 text-center">
                                    @if ($res->status == 'PENDING_PAYMENT')
                                        <a href="{{ route('booking.pay', $res->reservation_code) }}"
                                            class="inline-block rounded-full bg-ink px-5 py-2 text-xs font-semibold text-background transition-colors hover:bg-navy-dark hover:text-white">
                                            Bayar sekarang
                                        </a>
                                    @elseif (in_array($res->status, ['CONFIRMED', 'COMPLETED', 'CHECKED_IN', 'CHECKED_OUT']))
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('voucher.show', $res->reservation_code) }}"
                                                class="rounded-full border border-border bg-surface px-4 py-2 text-xs font-semibold text-ink transition-colors hover:border-ink">Lihat</a>
                                            <a href="{{ route('voucher.download', $res->reservation_code) }}" target="_blank"
                                                class="rounded-full bg-ink px-4 py-2 text-xs font-semibold text-background transition-colors hover:bg-navy-dark hover:text-white">PDF</a>
                                        </div>
                                    @else
                                        <span class="text-xs text-muted-text/60">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-14 text-center">
                                    <span class="material-symbols-outlined text-5xl text-muted-text/40">receipt_long</span>
                                    <p class="mt-4 text-ink">Belum ada riwayat reservasi.</p>
                                    <a href="{{ route('rooms.index') }}" class="mt-2 inline-block text-sm font-semibold text-navy-dark underline underline-offset-4">
                                        Mulai pesan sekarang
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($reservations->hasPages())
                <div class="border-t border-border/70 p-5">
                    {{ $reservations->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection