<?php

namespace App\Services;

use App\Models\Reservation;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class VoucherService
{
    /**
     * Generate QR Code
     */
    public function generateQrCode(string $code): string
    {
        $qrSvg = QrCode::size(150)
            ->format('svg')
            ->errorCorrection('H')
            ->generate($code);

        return 'data:image/svg+xml;base64,' . base64_encode($qrSvg);
    }

    /**
     * Generate PDF E-Voucher
     */
    public function generatePdfVoucher(Reservation $reservation)
    {
        $reservation->load(['roomBooking.roomType', 'hallBooking.hall', 'hallBooking.session', 'payments']);

        $qrCodeBase64 = $this->generateQrCode($reservation->reservation_code);

        $pdf = Pdf::loadView('pdf.e-voucher', [
            'reservation' => $reservation,
            'qrCode' => $qrCodeBase64,
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf;
    }
}