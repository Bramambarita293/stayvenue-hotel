<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use App\Models\Payment;
use App\Models\HallAvailability;
use App\Models\RoomDailyInventory;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;
use Midtrans\Config;
use Midtrans\Transaction;

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
                // Tembak API Midtrans menggunakan reservation_code
                $statusResponse = Transaction::status($reservation->reservation_code);
                $transactionStatus = $statusResponse->transaction_status ?? null;

                $payment = $reservation->payments->where('status', 'PENDING')->first();

                if (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
                    $this->cancelReservationAndReleaseInventory($reservation, $payment, 'EXPIRED');
                    $this->warn("Reservasi {$reservation->reservation_code} dibatalkan (Expired/Cancel).");
                } elseif (in_array($transactionStatus, ['settlement', 'capture'])) {
                    $this->confirmReservation($reservation, $payment);
                    $this->info("Reservasi {$reservation->reservation_code} disahkan (Settled).");
                }
            } catch (\Exception $e) {
                // Jika error 404 dari Midtrans (tamu belum pernah klik bayar / snap expired tanpa transaksi)
                if (str_contains($e->getMessage(), '404')) {
                    $this->cancelReservationAndReleaseInventory($reservation, null, 'EXPIRED');
                    $this->warn("Reservasi {$reservation->reservation_code} dibatalkan (404 - Not Found di Midtrans).");
                }
            }
        }
    }

    private function confirmReservation($reservation, $payment)
    {
        if ($payment) {
            $payment->update([
                'status' => 'SUCCESS',
                'paid_at' => now(),
            ]);
        }
        $reservation->update(['status' => 'CONFIRMED']);
    }

    private function cancelReservationAndReleaseInventory($reservation, $payment, $paymentStatus)
    {
        if ($payment) {
            $payment->update(['status' => $paymentStatus]);
        }
        $reservation->update(['status' => 'CANCELLED']);

        // 1. Release Hall
        if ($reservation->reservation_type === 'HALL') {
            HallAvailability::where('reservation_id', $reservation->id)->delete();
        }

        // 2. Release Room Inventory
        if ($reservation->reservation_type === 'ROOM' && $reservation->roomBooking) {
            $roomBooking = $reservation->roomBooking;
            $dates = CarbonPeriod::create($roomBooking->check_in_date, Carbon::parse($roomBooking->check_out_date)->subDay());

            foreach ($dates as $date) {
                RoomDailyInventory::where('room_type_id', $roomBooking->room_type_id)
                    ->where('date', $date->format('Y-m-d'))
                    ->decrement('booked_count', $roomBooking->number_of_rooms);
            }
        }
    }
}
