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
        Config::$isProduction = config('services.midtrans.is_production', false);

        // Cari reservasi pending yang umurnya lebih dari 24 jam
        $reservations = Reservation::with(['roomBooking', 'hallBooking', 'payments'])
            ->where('status', 'PENDING_PAYMENT')
            ->where('created_at', '<', now()->subHours(24))
            ->get();

        $this->info("Menemukan {$reservations->count()} reservasi pending.");

        foreach ($reservations as $reservation) {
            try {
                // Sertakan CHALLENGE agar transaksi fraud-review ikut rekonsiliasi.
                $payment = $reservation->payments->whereIn('status', ['PENDING', 'CHALLENGE'])->first();

                // Tanpa transaction_id valid JANGAN cancel: order belum tentu ada di Midtrans.
                // Kecuali payment INITIATED basi (>1 jam): Snap tak pernah terbit -> yatim, bersihkan.
                if (! $payment || ! $payment->transaction_id) {
                    $hasNewerValidPayment = $reservation->payments()
                        ->whereIn('status', ['PENDING', 'CHALLENGE'])
                        ->whereNotNull('transaction_id')
                        ->exists();

                    if ($hasNewerValidPayment) {
                        Log::info("midtrans:check-pending lewati {$reservation->reservation_code}: ada payment valid lebih baru.");
                        $this->warn("Reservasi {$reservation->reservation_code} dilewati (ada payment valid lebih baru).");
                        continue;
                    }

                    $staleInitiated = $reservation->payments
                        ->where('status', 'INITIATED')
                        ->where('created_at', '<', now()->subHour())
                        ->first();

                    if ($staleInitiated) {
                        $this->cancelReservationAndReleaseInventory($reservation, $staleInitiated, 'FAILED');
                        $this->warn("Reservasi {$reservation->reservation_code} dibatalkan (INITIATED basi).");

                        continue;
                    }

                    Log::warning("midtrans:check-pending lewati {$reservation->reservation_code}: tanpa transaction_id.");
                    $this->warn("Reservasi {$reservation->reservation_code} dilewati (tanpa transaction_id).");

                    continue;
                }

                // Query status WAJIB memakai order_id Snap yang persis
                // (tersimpan di payments.transaction_id), bukan reservation_code.
                $orderId = $payment->transaction_id;
                $statusResponse = Transaction::status($orderId);
                $transactionStatus = $statusResponse->transaction_status ?? null;
                $fraudStatus = $statusResponse->fraud_status ?? null;

                if (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
                    $this->cancelReservationAndReleaseInventory($reservation, $payment, $transactionStatus === 'expire' ? 'EXPIRED' : 'FAILED');
                    $this->warn("Reservasi {$reservation->reservation_code} dibatalkan (Expired/Cancel).");
                } elseif ($transactionStatus === 'settlement' || ($transactionStatus === 'capture' && $fraudStatus === 'accept')) {
                    DB::transaction(function () use ($reservation, $payment, $statusResponse) {
                        $fresh = Reservation::whereKey($reservation->id)->lockForUpdate()->first();
                        if (! $fresh || $fresh->status !== 'PENDING_PAYMENT') {
                            return;
                        }

                        $fresh->update(['status' => 'CONFIRMED']);
                        $payment?->update([
                            'status' => 'SUCCESS',
                            'payment_method' => $statusResponse->payment_type ?? $payment->payment_method,
                            'paid_at' => now(),
                        ]);
                    });
                    $this->info("Reservasi {$reservation->reservation_code} disahkan (Settled).");
                } elseif ($transactionStatus === 'capture' && $fraudStatus === 'challenge') {
                    if ($payment->status !== 'SUCCESS') {
                        $payment->update(['status' => 'CHALLENGE']);
                        $this->info("Reservasi {$reservation->reservation_code} dalam CHALLENGE.");
                    } else {
                        $this->info("Reservasi {$reservation->reservation_code} sudah SUCCESS, skip CHALLENGE.");
                    }
                }
            } catch (Throwable $e) {
                // 404 pada order_id valid = order hilang di Midtrans -> cancel.
                // Error lain (jaringan/rate-limit) JANGAN cancel, cukup log.
                if ((int) $e->getCode() === 404 || str_contains($e->getMessage(), '404')) {
                    $this->cancelReservationAndReleaseInventory($reservation, $reservation->payments->whereIn('status', ['PENDING', 'CHALLENGE'])->first(), 'EXPIRED');
                    $this->warn("Reservasi {$reservation->reservation_code} dibatalkan (404 - Not Found di Midtrans).");

                    continue;
                }

                Log::error("midtrans:check-pending gagal untuk {$reservation->reservation_code}: {$e->getMessage()}");
                $this->warn("Reservasi {$reservation->reservation_code} dilewati (error API).");
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
