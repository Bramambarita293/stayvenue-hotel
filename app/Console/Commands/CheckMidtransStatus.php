<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Command;
use Midtrans\Config;
use Midtrans\Transaction;
use Throwable;

class CheckMidtransStatus extends Command
{
    protected $signature = 'midtrans:check-pending';
    protected $description = 'Mengecek reservasi PENDING > 24 jam ke API Midtrans dan membersihkan inventory jika EXPIRED';

    public function handle()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');

        // Cari reservasi pending yang umurnya lebih dari 24 jam
        $reservations = Reservation::with(['roomBooking', 'hallBooking', 'payments'])
            ->where('status', 'PENDING_PAYMENT')
            ->where('created_at', '<', now()->subHours(24))
            ->get();

        $this->info("Menemukan {$reservations->count()} reservasi pending.");

        foreach ($reservations as $reservation) {
            try {
                $payment = $reservation->payments->where('status', 'PENDING')->first();

                // Query status WAJIB memakai order_id Snap yang persis
                // (tersimpan di payments.transaction_id), bukan reservation_code.
                $orderId = $payment?->transaction_id ?: $reservation->reservation_code;
                $statusResponse = Transaction::status($orderId);
                $transactionStatus = $statusResponse->transaction_status ?? null;

                if (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
                    $this->cancelReservationAndReleaseInventory($reservation, $payment, $transactionStatus === 'expire' ? 'EXPIRED' : 'FAILED');
                    $this->warn("Reservasi {$reservation->reservation_code} dibatalkan (Expired/Cancel).");
                } elseif (in_array($transactionStatus, ['settlement', 'capture'])) {
                    DB::transaction(function () use ($reservation, $payment) {
                        $fresh = Reservation::whereKey($reservation->id)->lockForUpdate()->first();
                        if (!$fresh || $fresh->status !== 'PENDING_PAYMENT') {
                            return;
                        }

                        $fresh->update(['status' => 'CONFIRMED']);
                        $payment?->update([
                            'status'   => 'SUCCESS',
                            'paid_at'  => now(),
                        ]);
                    });
                    $this->info("Reservasi {$reservation->reservation_code} disahkan (Settled).");
                }
            } catch (Throwable $e) {
                // 404 = order tidak pernah ada / token expired tanpa transaksi.
                if ((int) $e->getCode() === 404 || str_contains($e->getMessage(), '404')) {
                    $this->cancelReservationAndReleaseInventory($reservation, $reservation->payments->where('status', 'PENDING')->first(), 'EXPIRED');
                    $this->warn("Reservasi {$reservation->reservation_code} dibatalkan (404 - Not Found di Midtrans).");

                    continue;
                }

                Log::error("midtrans:check-pending gagal untuk {$reservation->reservation_code}: {$e->getMessage()}");
            }
        }
    }

    private function cancelReservationAndReleaseInventory(Reservation $reservation, ?Payment $payment, string $paymentStatus): void
    {
        DB::transaction(function () use ($reservation, $payment, $paymentStatus) {
            // Lock ulang & guard status agar idempoten terhadap webhook
            // atau polling user yang mungkin memproses reservasi yang sama.
            $fresh = Reservation::whereKey($reservation->id)->lockForUpdate()->first();

            if (!$fresh || $fresh->status !== 'PENDING_PAYMENT') {
                return;
            }

            $fresh->update(['status' => 'CANCELLED']);

            if ($payment) {
                Payment::whereKey($payment->id)->update(['status' => $paymentStatus]);
            }

            $fresh->releaseStock();
        });
    }
}
