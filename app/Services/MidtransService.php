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
     * Membuat Snap Token untuk transaksi Midtrans.
     *
     * @return array{0: string, 1: string} [order_id, snap_token]
     *         order_id HARUS disimpan (payments.transaction_id) karena semua
     *         pengecekan status ke API Midtrans wajib memakai order_id ini.
     */
    public function createSnapToken($reservation, $amount, $paymentType = 'FULL'): array
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $orderId = $reservation->reservation_code . '-' . time();

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) round($amount),
            ],
            'customer_details' => [
                'first_name' => $reservation->guest_name,
                'email' => $reservation->guest_email,
                'phone' => $reservation->guest_phone,
            ],
        ];

        return [$orderId, Snap::getSnapToken($params)];
    }

    /**
     * Ekstrak reservation_code dari order_id Midtrans ({code}-{unixtime}).
     * Robust: hanya strip suffix -<digits> di ujung, bukan explode('-').
     */
    public static function reservationCodeFromOrderId(string $orderId): string
    {
        $stripped = preg_replace('/-\d+$/', '', $orderId);

        return $stripped !== null && $stripped !== '' ? $stripped : $orderId;
    }
}
