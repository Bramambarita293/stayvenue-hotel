<?php

namespace App\Services;

use App\Models\Reservation;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = config('services.midtrans.is_sanitized');
        Config::$is3ds = config('services.midtrans.is_3ds');
    }

    /**
     * Membuat Snap Token untuk transaksi Midtrans
     */
    public function createSnapToken($reservation, $amount, $paymentType = 'FULL')
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $params = [
            'transaction_details' => [
                'order_id' => $reservation->reservation_code . '-' . time(),
                'gross_amount' => (int) $amount,
            ],
            'customer_details' => [
                'first_name' => $reservation->guest_name,
                'email' => $reservation->guest_email,
                'phone' => $reservation->guest_phone,
            ],
        ];

        return Snap::getSnapToken($params);
    }
}
