@extends('layouts.blank')

@section('title', 'Payment – ' . $reservation->reservation_code)

@section('content')
    <div class="flex min-h-screen items-center justify-center bg-background p-4">
        <div class="w-full max-w-md overflow-hidden rounded-2xl border border-line/70 bg-surface shadow-card">
            <div class="border-b border-line/70 bg-onyx px-8 py-7 text-center text-background">
                <span class="material-symbols-outlined text-gold">payments</span>
                <h1 class="mt-2 font-display text-3xl font-medium tracking-tight">
                    {{ $site['name'] }}<span class="text-gold">.</span>
                </h1>
                <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.25em] text-background/60">Selesaikan pembayaran Anda</p>
            </div>

            <div class="p-8">
                <div class="space-y-3 rounded-xl bg-surface-muted p-5">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-stone">Kode booking</span>
                        <span class="font-mono font-bold text-ink">{{ $reservation->reservation_code }}</span>
                    </div>
                    <div class="flex items-center justify-between border-t border-line/70 pt-3 text-sm">
                        <span class="text-stone">Kategori</span>
                        <span class="font-semibold text-ink">{{ $reservation->reservation_type == 'ROOM' ? 'Kamar Hotel' : 'Sewa Gedung / Ballroom' }}</span>
                    </div>
                    <div class="flex items-center justify-between border-t border-line/70 pt-3">
                        <span class="text-sm text-stone">Total tagihan</span>
                        <span class="font-display text-xl font-semibold text-ink">
                            Rp {{ number_format($reservation->total_amount, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                <button id="pay-button"
                    class="mt-6 flex w-full items-center justify-center gap-2 rounded-full bg-ink px-6 py-4 text-sm font-semibold uppercase tracking-wider text-background transition-all hover:-translate-y-0.5 hover:bg-gold-soft hover:text-white">
                    Bayar sekarang <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                </button>

                <a href="{{ route('user.reservations') }}"
                    class="mt-4 block text-center text-xs font-medium text-stone transition-colors hover:text-gold-soft">
                    Bayar nanti (kembali ke riwayat)
                </a>
            </div>
        </div>
    </div>

    <script type="text/javascript"
        src="{{ config('services.midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
        data-client-key="{{ config('services.midtrans.client_key') }}"></script>
    <script type="text/javascript">
        var payButton = document.getElementById('pay-button');
        var isSnapOpen = false;

        payButton.addEventListener('click', function () {
            if (isSnapOpen) {
                console.warn("Pop-up Snap sudah terbuka!");
                return;
            }

            @if (isset($payment) && $payment->snap_token)
                payButton.disabled = true;
                payButton.classList.add('opacity-50', 'cursor-not-allowed');
                payButton.innerText = 'Memuat pembayaran...';
                isSnapOpen = true;

                window.snap.pay('{{ $payment->snap_token }}', {
                    onSuccess: function (result) {
                        isSnapOpen = false;
                        window.location.href = "{{ route('user.reservations') }}";
                    },
                    onPending: function (result) {
                        isSnapOpen = false;
                        window.location.href = "{{ route('user.reservations') }}";
                    },
                    onError: function (result) {
                        isSnapOpen = false;
                        alert("Pembayaran gagal! Silakan coba lagi.");
                        resetPayButton();
                    },
                    onClose: function () {
                        isSnapOpen = false;
                        resetPayButton();
                    }
                });
            @else
                alert("Token pembayaran tidak ditemukan atau kadaluarsa. Silakan muat ulang halaman.");
            @endif
        });

        function resetPayButton() {
            payButton.disabled = false;
            payButton.classList.remove('opacity-50', 'cursor-not-allowed');
            payButton.innerText = 'Bayar sekarang';
        }
    </script>
@endsection