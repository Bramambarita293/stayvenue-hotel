<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>E-Voucher - {{ $reservation->reservation_code }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            color: #101828;
            margin: 0;
            padding: 20px;
            font-size: 13px;
        }
        .header {
            border-bottom: 2px solid #101828;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header table {
            width: 100%;
        }
        .hotel-name {
            font-size: 22px;
            font-weight: bold;
            color: #101828;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        .voucher-title {
            font-size: 14px;
            font-weight: bold;
            color: #1E3A8A;
            text-align: right;
            text-transform: uppercase;
            letter-spacing: 0.12em;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: bold;
            color: #fff;
            background-color: #101828;
            border-radius: 4px;
            letter-spacing: 0.06em;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #101828;
            background-color: #EDF1F7;
            padding: 6px 10px;
            border-left: 4px solid #1E3A8A;
            margin-top: 20px;
            margin-bottom: 10px;
            letter-spacing: 0.06em;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .info-table td {
            padding: 6px 4px;
            vertical-align: top;
            border-bottom: 1px solid #E2E8F0;
        }
        .info-table .label {
            width: 30%;
            color: #5B6472;
            font-weight: 500;
        }
        .info-table .value {
            width: 70%;
            font-weight: bold;
        }
        .qr-container {
            text-align: center;
            padding: 10px;
            background: #F7F9FC;
            border: 1px dashed #93B0E8;
            border-radius: 6px;
        }
        .qr-container img {
            width: 130px;
            height: 130px;
        }
        .footer {
            margin-top: 40px;
            border-top: 1px solid #E2E8F0;
            padding-top: 10px;
            font-size: 11px;
            color: #8A94A6;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <div class="header">
        <table>
            <tr>
                <td>
                    <div class="hotel-name">STAY VENUE HOTEL</div>
                    <div style="color: #5B6472; font-size: 11px;">Jl. Raya Utama No. 1, Menteng, Jakarta Pusat 10310 • Telp: +62 21 555-0199</div>
                </td>
                <td style="text-align: right;">
                    <div class="voucher-title">E-VOUCHER CONFIRMATION</div>
                    <div style="font-size: 12px; font-weight: bold; margin-top: 5px;">{{ $reservation->reservation_code }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Main Content Grid -->
    <table style="width: 100%;">
        <tr>
            <!-- Left Column: Guest & Booking Details -->
            <td style="width: 65%; vertical-align: top;">
                <div class="section-title">INFORMASI TAMU</div>
                <table class="info-table">
                    <tr>
                        <td class="label">Nama Tamu:</td>
                        <td class="value">{{ $reservation->guest_name }}</td>
                    </tr>
                    <tr>
                        <td class="label">Email:</td>
                        <td class="value">{{ $reservation->guest_email }}</td>
                    </tr>
                    <tr>
                        <td class="label">No. Telepon:</td>
                        <td class="value">{{ $reservation->guest_phone }}</td>
                    </tr>
                </table>

                <div class="section-title">DETAIL RESERVASI ({{ $reservation->reservation_type == 'ROOM' ? 'KAMAR HOTEL' : 'SEWA GEDUNG' }})</div>
                <table class="info-table">
                    @if($reservation->reservation_type == 'ROOM')
                        <tr>
                            <td class="label">Tipe Kamar:</td>
                            <td class="value">{{ $reservation->roomBooking->roomType->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Tanggal Check-In:</td>
                            <td class="value">{{ \Carbon\Carbon::parse($reservation->roomBooking->check_in_date)->format('d M Y') }} (14:00 WIB)</td>
                        </tr>
                        <tr>
                            <td class="label">Tanggal Check-Out:</td>
                            <td class="value">{{ \Carbon\Carbon::parse($reservation->roomBooking->check_out_date)->format('d M Y') }} (12:00 WIB)</td>
                        </tr>
                        <tr>
                            <td class="label">Jumlah Kamar:</td>
                            <td class="value">{{ $reservation->roomBooking->number_of_rooms }} Unit</td>
                        </tr>
                    @else
                        <tr>
                            <td class="label">Gedung / Room:</td>
                            <td class="value">{{ $reservation->hallBooking->hall->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Tanggal Acara:</td>
                            <td class="value">{{ \Carbon\Carbon::parse($reservation->hallBooking->event_date)->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td class="label">Sesi / Slot:</td>
                            <td class="value">{{ $reservation->hallBooking->session->session_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Jenis Acara:</td>
                            <td class="value">{{ $reservation->hallBooking->event_type }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="label">Total Pembayaran:</td>
                        <td class="value" style="color: #172E6E; font-size: 15px;">Rp {{ number_format($reservation->total_amount, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Status:</td>
                        <td class="value"><span class="badge">LUNAS / CONFIRMED</span></td>
                    </tr>
                </table>
            </td>

            <!-- Right Column: QR Code Check-in -->
            <td style="width: 35%; vertical-align: top; padding-left: 20px;">
                <div class="qr-container">
                    <div style="font-weight: bold; font-size: 11px; margin-bottom: 8px; color: #5B6472;">SCAN UNTUK CHECK-IN</div>
                    <img src="{{ $qrCode }}" alt="QR Code Check-in">
                    <div style="font-size: 10px; color: #8A94A6; margin-top: 8px;">Tunjukkan QR Code ini ke resepsionis saat kedatangan.</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Footer Note -->
    <div class="footer">
        E-Voucher ini diterbitkan secara otomatis oleh sistem. Harap membawa identitas asli (KTP/Paspor) saat proses Check-in.<br>
        © {{ date('Y') }} Stay Venue Hotel. All rights reserved.
    </div>

</body>
</html>