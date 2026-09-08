<?php

namespace App\Services;

use App\Models\Reservation;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production', false);
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

        // Separator ganda '--' agar tak ambigu dengan '-' di dalam kode
        // (kode acak bisa full-digit, mis. HTL-20240101-12345).
        // Suffix acak anti tabrakan order sedetik sama (refresh double-click).
        $orderId = $reservation->reservation_code.'--'.time().'-'.strtoupper(Str::random(4));

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) round($amount),
            ],
            // Selaras kebijakan hold 24 jam: order kedaluwarsa serentak dengan sapu cron.
            'expiry' => [
                'start_time' => date('Y-m-d H:i:s O'),
                'unit' => 'hour',
                'duration' => 24,
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
     * Ekstrak reservation_code dari order_id Midtrans ({code}--{unixtime}-{rand}).
     * Format lama ({code}-{unixtime} / {code}--{unixtime}) tetap didukung untuk order in-flight.
     */
    public static function reservationCodeFromOrderId(string $orderId): string
    {
        $pos = strrpos($orderId, '--');
        if ($pos !== false) {
            $code = substr($orderId, 0, $pos);

            return $code !== '' ? $code : $orderId;
        }

        $stripped = preg_replace('/-\d+$/', '', $orderId);

        return $stripped !== null && $stripped !== '' ? $stripped : $orderId;
    }
}
