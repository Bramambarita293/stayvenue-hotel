<?php

namespace App\Http\Controllers;

use App\Models\HallAvailability;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomDailyInventory;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Notification;

class MidtransWebhookController extends Controller
{
    public function handleNotification(Request $request)
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production', false);

        Log::info('Midtrans Webhook Payload:', $request->all());

        try {
            $notif = new Notification();
        } catch (\Exception $e) {
            Log::error('Midtrans Notification Error: ' . $e->getMessage());
            return response()->json(['message' => 'Invalid Notification'], 400);
        }

        $transactionStatus = $notif->transaction_status;
        $type = $notif->payment_type;
        $orderId = $notif->order_id;
        $fraudStatus = $notif->fraud_status;

        $parts = explode('-', $orderId);
        if (count($parts) > 3) {
            array_pop($parts);
        }
        $reservationCode = implode('-', $parts);

        $reservation = Reservation::where('reservation_code', $reservationCode)->first();

        if (!$reservation) {
            Log::error("Reservation dengan kode '{$reservationCode}' dari order_id '{$orderId}' tidak ditemukan di database.");
            return response()->json(['message' => 'Reservation not found'], 404);
        }

        $payment = Payment::where('reservation_id', $reservation->id)->latest()->first();

        DB::transaction(function () use ($transactionStatus, $type, $fraudStatus, $reservation, $payment, $orderId) {
            if ($transactionStatus == 'settlement' || ($transactionStatus == 'capture' && $fraudStatus == 'accept')) {

                $reservation->update(['status' => 'CONFIRMED']);

                if ($payment) {
                    $payment->update([
                        'transaction_id' => $orderId,
                        'status'         => 'SUCCESS',
                        'payment_method' => $type,
                        'paid_at'        => now(),
                    ]);
                }

                Log::info("STATUS RESERVASI {$reservation->reservation_code} CONFIRMED.");
            } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {

                $reservation->update(['status' => 'CANCELLED']);

                if ($payment) {
                    $payment->update(['status' => 'EXPIRED']);
                }

                if ($reservation->roomBooking && $reservation->roomBooking->room_id) {
                    $room = Room::find($reservation->roomBooking->room_id);
                    if ($room && $room->status === 'OCCUPIED') {
                        $room->update(['status' => 'AVAILABLE']);
                    }

                    $reservation->roomBooking->update([
                        'room_id' => null,
                        'assigned_room_number' => null
                    ]);
                }

                if ($reservation->reservation_type === 'HALL') {
                    HallAvailability::where('reservation_id', $reservation->id)->delete();
                } elseif ($reservation->reservation_type === 'ROOM' && $reservation->roomBooking) {
                    $roomBooking = $reservation->roomBooking;
                    $dates = CarbonPeriod::create($roomBooking->check_in_date, Carbon::parse($roomBooking->check_out_date)->subDay());

                    foreach ($dates as $date) {
                        RoomDailyInventory::where('room_type_id', $roomBooking->room_type_id)
                            ->where('date', $date->format('Y-m-d'))
                            ->decrement('booked_count', $roomBooking->number_of_rooms);
                    }
                }

                Log::info("RESERVASI {$reservation->reservation_code} DIBATALKAN DAN STOK KEMBALI DI-RELEASE.");
            }
        });

        return response()->json(['message' => 'Notification processed successfully'], 200)
            ->header('ngrok-skip-browser-warning', 'true');
    }
}
