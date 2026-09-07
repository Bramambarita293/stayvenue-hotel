<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Services\VoucherService;
use Illuminate\Support\Facades\Gate;

class VoucherController extends Controller
{
    private const AVAILABLE_STATUSES = ['CONFIRMED', 'COMPLETED', 'CHECKED_IN', 'CHECKED_OUT'];

    public function download(string $code, VoucherService $voucherService)
    {
        // Pemilik reservasi atau admin; mencegah user lain mendownload
        // e-voucher milik orang lain (IDOR) dengan menebak kode booking.
        $reservation = Reservation::where('reservation_code', $code)
            ->when(!auth()->user()?->is_admin, fn ($query) => $query->where('user_id', auth()->id()))
            ->firstOrFail();

        Gate::authorize('download', $reservation);

        if (!in_array($reservation->status, self::AVAILABLE_STATUSES)) {
            return response()->json(['message' => 'Voucher belum tersedia, pembayaran belum terkonfirmasi.'], 403);
        }

        $pdf = $voucherService->generatePdfVoucher($reservation);

        return $pdf->download('E-Voucher-' . $reservation->reservation_code . '.pdf');
    }

    /**
     * Tampilkan Halaman Web E-Voucher
     */
    public function show(string $code, VoucherService $voucherService)
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
            ->when(!auth()->user()?->is_admin, fn ($query) => $query->where('user_id', auth()->id()))
            ->firstOrFail();

        Gate::authorize('view', $reservation);

        if (!in_array($reservation->status, self::AVAILABLE_STATUSES)) {
            return redirect()->route('user.reservations')
                ->with('error', 'E-Voucher hanya tersedia untuk pesanan yang sudah lunas.');
        }

        // QR dibuat lokal (Simple-QRcode), tidak mengirim kode booking
        // ke layanan QR pihak ketiga.
        $qrCode = $voucherService->generateQrCode($reservation->reservation_code);

        return view('voucher.show', compact('reservation', 'qrCode'));
    }
}
