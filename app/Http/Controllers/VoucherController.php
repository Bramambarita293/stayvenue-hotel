<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Services\VoucherService;

class VoucherController extends Controller
{
    public function download(string $code, VoucherService $voucherService)
    {
        $reservation = Reservation::where('reservation_code', $code)->firstOrFail();

        if ($reservation->status !== 'CONFIRMED' && $reservation->status !== 'COMPLETED') {
            return response()->json(['message' => 'Voucher belum tersedia, pembayaran belum terkonfirmasi.'], 403);
        }

        $pdf = $voucherService->generatePdfVoucher($reservation);

        return $pdf->download('E-Voucher-' . $reservation->reservation_code . '.pdf');
    }

    /**
     * Tampilkan Halaman Web E-Voucher
     */
    public function show(string $code)
    {
        $reservation = Reservation::with([
            'roomBooking.roomType',
            'hallBooking.hall',
            'hallBooking.session',
            'hallBooking.eventPackage',
            'payments' => function ($query) {
                $query->where('status', 'SUCCESS')->latest();
            }
        ])
            ->where('reservation_code', $code)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if (!in_array($reservation->status, ['CONFIRMED', 'COMPLETED'])) {
            return redirect()->route('user.reservations')
                ->with('error', 'E-Voucher hanya tersedia untuk pesanan yang sudah lunas.');
        }

        return view('voucher.show', compact('reservation'));
    }
}
