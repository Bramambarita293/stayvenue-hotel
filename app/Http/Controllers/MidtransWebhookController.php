<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Notification;

class MidtransWebhookController extends Controller
{
    public function handleNotification(Request $request)
    {
        $serverKey = config('services.midtrans.server_key');
        Config::$serverKey = $serverKey;
        Config::$isProduction = config('services.midtrans.is_production', false);

        $orderId = (string) $request->input('order_id', '');
        $statusCode = (string) $request->input('status_code', '');
        $grossAmount = $request->input('gross_amount');
        $signatureKey = (string) $request->input('signature_key', '');

        // Verifikasi signature WAJIB: tanpa 3 field + signature cocok, tolak.
        // Mencegah spoof settlement -> CONFIRMED gratis via POST palsu.
        if ($orderId === '' || $statusCode === '' || $grossAmount === null || $signatureKey === '') {
            Log::warning('Midtrans webhook ditolak: field signature tidak lengkap.');

            return response()->json(['message' => 'Incomplete notification'], 403);
        }

        $expected = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);
        if (! hash_equals($expected, $signatureKey)) {
            Log::warning('Midtrans webhook signature invalid.', ['order_id' => $orderId]);

            return response()->json(['message' => 'Invalid signature'], 403);
        }

        Log::info('Midtrans Webhook received.', ['order_id' => $orderId]);

        try {
            $notif = new Notification();
        } catch (\Exception $e) {
            Log::error('Midtrans Notification Error: ' . $e->getMessage());

            return response()->json(['message' => 'Invalid Notification'], 400);
        }

        $transactionStatus = $notif->transaction_status ?? null;
        $type = $notif->payment_type ?? 'MIDTRANS';
        // Trust boundary: order_id hasil parse SDK wajib sama dengan yang
        // sudah terverifikasi signature-nya. Beda -> body diutak-atik.
        $notifOrderId = (string) ($notif->order_id ?? '');
        if ($notifOrderId !== $orderId) {
            Log::warning('Midtrans webhook order mismatch.', ['signed' => $orderId, 'body' => $notifOrderId]);

            return response()->json(['message' => 'Order mismatch'], 403);
        }
        $fraudStatus = $notif->fraud_status ?? null;
        $grossAmount = $notif->gross_amount ?? null;
        $reservationCode = \App\Services\MidtransService::reservationCodeFromOrderId($orderId);

        // Seluruh pembacaan + penulisan status dilakukan dalam SATU transaksi
        // dengan lockForUpdate agar webhook yang di-retry Midtrans bersifat
        // idempoten (stok tidak pernah ter-release dua kali).
        $result = DB::transaction(function () use ($transactionStatus, $type, $fraudStatus, $grossAmount, $reservationCode) {
            $reservation = Reservation::where('reservation_code', $reservationCode)
                ->lockForUpdate()
                ->first();

            if (!$reservation) {
                return 'not_found';
            }

            if ($reservation->status !== 'PENDING_PAYMENT') {
                // Sudah CONFIRMED / CANCELLED sebelumnya — jangan proses ulang.
                Log::info("Webhook diabaikan: reservasi {$reservationCode} sudah berstatus {$reservation->status}.");

                return 'skipped';
            }

            $payment = $reservation->payments()->whereIn('status', ['PENDING', 'CHALLENGE'])->latest()->first();

            // Verifikasi nominal: bandingkan sebagai integer rupiah agar presisi aman.
            if ($payment && $grossAmount !== null && (int) round((float) $grossAmount) !== (int) round((float) $payment->amount)) {
                Log::warning("Gross amount mismatch untuk {$reservationCode}: notif={$grossAmount}, db={$payment->amount}.");

                return 'amount_mismatch';
            }

            if ($transactionStatus === 'settlement' || ($transactionStatus === 'capture' && $fraudStatus === 'accept')) {
                $reservation->update(['status' => 'CONFIRMED']);

                $payment?->update([
                    'transaction_id' => $orderId,
                    'status'         => 'SUCCESS',
                    'payment_method' => $type,
                    'paid_at'        => now(),
                ]);

                Log::info("STATUS RESERVASI {$reservation->reservation_code} CONFIRMED.");

                return 'confirmed';
            }

            if ($transactionStatus === 'capture' && $fraudStatus === 'challenge') {
                // Uang masuk tapi tertahan review fraud — tandai CHALLENGE,
                // reservasi tetap PENDING_PAYMENT sampai status lanjutan datang.
                $payment?->update([
                    'transaction_id' => $orderId,
                    'status'         => 'CHALLENGE',
                    'payment_method' => $type,
                ]);

                Log::warning("Reservasi {$reservation->reservation_code} dalam CHALLENGE (fraud review).");

                return 'challenge';
            }

            if (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                $reservation->update(['status' => 'CANCELLED']);

                $paymentStatus = $transactionStatus === 'expire' ? 'EXPIRED' : 'FAILED';
                $payment?->update(['status' => $paymentStatus]);

                $roomBooking = $reservation->roomBooking;
                if ($roomBooking && $roomBooking->room_id) {
                    $room = \App\Models\Room::whereKey($roomBooking->room_id)->lockForUpdate()->first();
                    // Hanya kembalikan kamar yang OCCUPIED; MAINTENANCE/CLEANING dipertahankan.
                    if ($room && $room->status === 'OCCUPIED') {
                        $room->update(['status' => 'AVAILABLE']);
                    }
                    $roomBooking->update([
                        'room_id' => null,
                        'assigned_room_number' => null,
                    ]);
                }

                $reservation->releaseStock();

                Log::info("RESERVASI {$reservation->reservation_code} DIBATALKAN DAN STOK KEMBALI DI-RELEASE ({$transactionStatus}).");

                return 'cancelled';
            }

            return 'ignored';
        });

        return match ($result) {
            'not_found' => response()->json(['message' => 'Reservation not found'], 404),
            // amount_mismatch/skipped/ignored tetap 200 agar Midtrans tidak retry tak berujung.
            default => response()->json(['message' => 'Notification processed successfully'], 200)
                ->header('ngrok-skip-browser-warning', 'true'),
        };
    }
}
