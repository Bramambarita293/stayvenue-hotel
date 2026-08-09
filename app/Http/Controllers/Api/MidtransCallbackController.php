<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HallAvailability;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\RoomDailyInventory;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Notification;

class MidtransCallbackController extends Controller
{
    public function handleCallback(Request $request): JsonResponse
    {
        // Setup konfigurasi Midtrans
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');

        try {
            $notification = new Notification();

            $transactionStatus = $notification->transaction_status;
            $orderId = $notification->order_id;
            $paymentMethod = $notification->payment_type ?? null;
            $fraudStatus = $notification->fraud_status ?? null;

            // Karena orderId sekarang murni berisi reservation_code (contoh: HTL-20260722-X89),
            // kita tidak perlu lagi menggunakan explode()
            $reservationCode = $orderId;

            // 1. CARI RESERVASI
            $reservation = Reservation::where('reservation_code', $reservationCode)->first();

            if (!$reservation) {
                return response()->json(['message' => 'Reservation not found'], 404);
            }

            // 2. CARI PAYMENT TERKAIT
            $payment = Payment::where([
                ['reservation_id', '=', $reservation->id],
                ['status', '=', 'PENDING'],
            ])->latest()->first();

            // Logika Perubahan Status Berdasarkan Respon Midtrans
            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'challenge') {
                    $this->updateStatus($reservation, $payment, 'CHALLENGE', $orderId, $paymentMethod);
                } else if ($fraudStatus == 'accept') {
                    $this->updateStatus($reservation, $payment, 'SETTLED', $orderId, $paymentMethod);
                }
            } else if ($transactionStatus == 'settlement') {
                $this->updateStatus($reservation, $payment, 'SETTLED', $orderId, $paymentMethod);
            } else if (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
                $this->updateStatus($reservation, $payment, 'FAILED', $orderId, $paymentMethod);
            } else if ($transactionStatus == 'pending') {
                $this->updateStatus($reservation, $payment, 'PENDING', $orderId, $paymentMethod);
            }

            return response()->json(['message' => 'Callback processed successfully']);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Callback error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function updateStatus(Reservation $reservation, ?Payment $payment, string $status, string $transactionId, ?string $paymentMethod): void
    {
        if ($payment) {
            $payment->update([
                'transaction_id' => $transactionId,
                'payment_method' => $paymentMethod,
                'status' => $status === 'SETTLED' ? 'SUCCESS' : $status,
                'paid_at' => $status === 'SETTLED' ? now() : null,
            ]);
        }

        if ($status === 'SETTLED') {
            $reservation->update(['status' => 'CONFIRMED']);
        } else if (in_array($status, ['FAILED', 'EXPIRED', 'CANCELLED'])) {
            $reservation->update(['status' => 'CANCELLED']);

            // 1. Release Status Kalender (Gedung)
            if ($reservation->reservation_type === 'HALL') {
                HallAvailability::where('reservation_id', $reservation->id)->delete();
            }

            // 2. Release Kuota Stok Inventory Harian (Kamar Hotel)
            if ($reservation->reservation_type === 'ROOM' && $reservation->roomBooking) {
                $roomBooking = $reservation->roomBooking;
                // Ambil rentang waktu hari check-in sampai H-1 check-out
                $dates = CarbonPeriod::create($roomBooking->check_in_date, Carbon::parse($roomBooking->check_out_date)->subDay());
                
                foreach ($dates as $date) {
                    RoomDailyInventory::where('room_type_id', $roomBooking->room_type_id)
                        ->where('date', $date->format('Y-m-d'))
                        ->decrement('booked_count', $roomBooking->number_of_rooms);
                }
            }
        }
    }
}