<?php

namespace App\Http\Controllers;

use App\Models\EventPackage;
use App\Models\Hall;
use App\Models\HallAvailability;
use App\Models\HallBooking;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\RoomBooking;
use App\Models\RoomType;
use App\Models\RoomDailyInventory;
use App\Services\MidtransService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Transaction;

class BookingController extends Controller
{
    /**
     * Proses Checkout Kamar Hotel
     */
    public function checkoutRoom(Request $request, MidtransService $midtransService)
    {
        $request->validate([
            'room_type_id'    => 'required|exists:room_types,id',
            'check_in_date'   => 'required|date|after_or_equal:today',
            'check_out_date'  => 'required|date|after:check_in_date',
            'number_of_rooms' => 'required|integer|min:1',
        ]);

        $roomType = RoomType::findOrFail($request->room_type_id);
        $user = Auth::user();

        // 1. Hitung total fisik & kalkulasi biaya
        $totalPhysicalRooms = $roomType->total_inventory; // Menggunakan Accessor dinamis

        if ($totalPhysicalRooms <= 0) {
            return back()->withErrors([
                'check_in_date' => 'Maaf, belum ada unit kamar fisik yang tersedia untuk tipe ini.'
            ]);
        }

        $checkIn  = Carbon::parse($request->check_in_date);
        $checkOut = Carbon::parse($request->check_out_date);
        $requestedRooms = $request->number_of_rooms;
        $nights = $checkIn->diffInDays($checkOut);
        $totalAmount = $roomType->base_price * $nights * $requestedRooms;

        $period = CarbonPeriod::create($checkIn, $checkOut->copy()->subDay());

        // 2. VALIDASI STOK UNTUK SETIAP HARI MENGINAP
        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');

            $dailyInventory = RoomDailyInventory::firstOrCreate(
                ['room_type_id' => $roomType->id, 'date' => $dateString],
                ['booked_count' => 0]
            );

            $available = $totalPhysicalRooms - $dailyInventory->booked_count;

            if ($available < $requestedRooms) {
                return back()->withErrors([
                    'check_in_date' => "Kamar penuh pada tanggal {$date->format('d M Y')}. Sisa ketersediaan: {$available} unit."
                ]);
            }
        }

        // 3. PROSES TRANSAKSI & PENGUNCIAN STOK
        return DB::transaction(function () use ($request, $user, $roomType, $totalAmount, $midtransService, $period, $requestedRooms) {
            // A. Buat Parent Reservation
            $reservationCode = 'HTL-' . date('Ymd') . '-' . strtoupper(Str::random(5));
            $reservation = Reservation::create([
                'reservation_code' => $reservationCode,
                'user_id'          => $user->id,
                'guest_name'        => $user->name,
                'guest_email'       => $user->email,
                'guest_phone'       => $user->phone_number ?? '08123456789',
                'reservation_type' => 'ROOM',
                'total_amount'     => $totalAmount,
                'status'           => 'PENDING_PAYMENT',
            ]);

            // B. Buat Child RoomBooking
            RoomBooking::create([
                'reservation_id'   => $reservation->id,
                'room_type_id'     => $roomType->id,
                'check_in_date'    => $request->check_in_date,
                'check_out_date'   => $request->check_out_date,
                'number_of_rooms'  => $requestedRooms,
                'special_requests' => $request->special_requests ?? null,
            ]);

            // C. Kunci Stok Harian Sementara saat Checkout
            foreach ($period as $date) {
                $inventory = RoomDailyInventory::firstOrCreate(
                    ['room_type_id' => $roomType->id, 'date' => $date->format('Y-m-d')],
                    ['booked_count' => 0]
                );
                $inventory->increment('booked_count', $requestedRooms);
            }

            // D. Generate Midtrans Snap Token & Record Payment
            $snapToken = $midtransService->createSnapToken($reservation, $totalAmount, 'FULL');

            Payment::create([
                'reservation_id'  => $reservation->id,
                'transaction_id'  => $reservation->reservation_code,
                'payment_type'    => 'FULL',
                'amount'          => $totalAmount,
                'payment_gateway' => 'MIDTRANS',
                'snap_token'      => $snapToken,
                'status'           => 'PENDING',
            ]);

            return redirect()->route('booking.pay', $reservation->reservation_code);
        });
    }

    /**
     * Halaman Pembayaran Snap Midtrans
     */
    public function showPaymentPage(string $code)
    {
        $reservation = Reservation::where('reservation_code', $code)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $payment = Payment::where('reservation_id', $reservation->id)
            ->where('status', 'PENDING')
            ->latest()
            ->firstOrFail();

        return view('booking.pay', compact('reservation', 'payment'));
    }

    /**
     * Proses Checkout Sewa Gedung Acara
     */
    public function checkoutHall(Request $request, MidtransService $midtransService)
    {
        $request->validate([
            'hall_id'          => 'required|exists:halls,id',
            'session_id'        => 'required|exists:hall_sessions,id',
            'event_date'        => 'required|date|after_or_equal:today',
            'event_type'        => 'required|string|max:100',
            'event_package_id' => 'nullable|exists:event_packages,id',
        ]);

        $hall = Hall::findOrFail($request->hall_id);
        $user = Auth::user();

        // 1. Cek Ketersediaan Gedung
        $isBooked = HallAvailability::where([
            ['hall_id', '=', $request->hall_id],
            ['session_id', '=', $request->session_id],
            ['event_date', '=', $request->event_date],
        ])->exists();

        if ($isBooked) {
            return back()->withErrors(['event_date' => 'Gedung tidak tersedia pada tanggal dan sesi terpilih.']);
        }

        // 2. Hitung Biaya
        $totalAmount = $hall->base_rental_price;
        if ($request->filled('event_package_id')) {
            $package = EventPackage::findOrFail($request->event_package_id);
            $totalAmount += $package->price;
        }

        return DB::transaction(function () use ($request, $user, $hall, $totalAmount, $midtransService) {
            $reservationCode = 'HTL-' . date('Ymd') . '-' . strtoupper(Str::random(5));
            $reservation = Reservation::create([
                'reservation_code' => $reservationCode,
                'user_id'          => $user->id,
                'guest_name'        => $user->name,
                'guest_email'       => $user->email,
                'guest_phone'       => $user->phone_number ?? '08123456789',
                'reservation_type' => 'HALL',
                'total_amount'     => $totalAmount,
                'status'           => 'PENDING_PAYMENT',
            ]);

            HallBooking::create([
                'reservation_id'   => $reservation->id,
                'hall_id'          => $hall->id,
                'event_package_id' => $request->event_package_id ?? null,
                'session_id'       => $request->session_id,
                'event_date'       => $request->event_date,
                'event_type'       => $request->event_type,
                'special_notes'    => $request->special_notes ?? null,
            ]);

            HallAvailability::create([
                'hall_id'        => $hall->id,
                'session_id'     => $request->session_id,
                'event_date'     => $request->event_date,
                'reservation_id' => $reservation->id,
                'status'         => 'LOCKED',
            ]);

            $snapToken = $midtransService->createSnapToken($reservation, $totalAmount, 'FULL');

            Payment::create([
                'reservation_id'  => $reservation->id,
                'transaction_id'  => $reservation->reservation_code,
                'payment_type'    => 'FULL',
                'amount'          => $totalAmount,
                'payment_gateway' => 'MIDTRANS',
                'snap_token'      => $snapToken,
                'status'           => 'PENDING',
            ]);

            return redirect()->route('booking.pay', $reservation->reservation_code);
        });
    }

    /**
     * Dashboard Riwayat Reservasi Pelanggan
     */
    public function userReservations()
    {
        $reservations = Reservation::with(['roomBooking.roomType', 'hallBooking.hall', 'payments'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production', false);

        foreach ($reservations as $res) {
            if ($res->status === 'PENDING_PAYMENT') {
                $payment = $res->payments->where('status', 'PENDING')->first();
                if ($payment) {
                    try {
                        $lookupCode = $payment->transaction_id ?? $res->reservation_code;
                        $midtransStatus = Transaction::status($lookupCode);
                        $status = $midtransStatus['transaction_status'] ?? null;

                        if (in_array($status, ['settlement', 'capture'])) {
                            $res->update(['status' => 'CONFIRMED']);
                            $payment->update([
                                'status'         => 'SUCCESS',
                                'payment_method' => $midtransStatus['payment_type'] ?? 'MIDTRANS',
                                'paid_at'        => now(),
                            ]);
                        } elseif (in_array($status, ['deny', 'expire', 'cancel'])) {
                            $res->update(['status' => 'CANCELLED']);
                            $payment->update(['status' => 'EXPIRED']);

                            // Release stok jika transaksi expired via polling
                            if ($res->reservation_type === 'ROOM' && $res->roomBooking) {
                                $roomBooking = $res->roomBooking;
                                $dates = CarbonPeriod::create($roomBooking->check_in_date, Carbon::parse($roomBooking->check_out_date)->subDay());
                                foreach ($dates as $date) {
                                    RoomDailyInventory::where('room_type_id', $roomBooking->room_type_id)
                                        ->where('date', $date->format('Y-m-d'))
                                        ->decrement('booked_count', $roomBooking->number_of_rooms);
                                }
                            }
                        }
                    } catch (\Exception $e) {
                    }
                }
            }
        }

        return view('user.reservations', compact('reservations'));
    }
}
