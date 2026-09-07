<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutHallRequest;
use App\Http\Requests\CheckoutRoomRequest;
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
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Midtrans\Config;
use Midtrans\Transaction;
use Throwable;

class BookingController extends Controller
{
    /**
     * Ambil baris inventory harian sambil menguncinya (lockForUpdate).
     * Membuat baris bila belum ada; jika kalah race unique-index,
     * ambil ulang baris milik request lain dengan lock.
     */
    private function lockInventoryRow(int $roomTypeId, string $dateString): RoomDailyInventory
    {
        $inventory = RoomDailyInventory::where('room_type_id', $roomTypeId)
            ->where('date', $dateString)
            ->lockForUpdate()
            ->first();

        if ($inventory) {
            return $inventory;
        }

        try {
            return RoomDailyInventory::create([
                'room_type_id' => $roomTypeId,
                'date'         => $dateString,
                'booked_count' => 0,
            ]);
        } catch (QueryException $e) {
            if (($e->errorInfo[1] ?? null) == 1062) {
                return RoomDailyInventory::where('room_type_id', $roomTypeId)
                    ->where('date', $dateString)
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            throw $e;
        }
    }

    /**
     * Proses Checkout Kamar Hotel.
     * Hold stok 24 jam dipertahankan (keputusan bisnis); mitigasi borong via
     * max 5 kamar + max 14 malam + throttle route + Snap di luar transaksi.
     */
    public function checkoutRoom(CheckoutRoomRequest $request, MidtransService $midtransService)
    {
        $validated = $request->validated();

        $roomType = RoomType::findOrFail($validated['room_type_id']);
        $user = Auth::user();

        $checkIn = Carbon::parse($validated['check_in_date']);
        $checkOut = Carbon::parse($validated['check_out_date']);
        $requestedRooms = (int) $validated['number_of_rooms'];
        $nights = $checkIn->diffInDays($checkOut);
        $totalAmount = $roomType->base_price * $nights * $requestedRooms;

        $periodDates = [];
        foreach (CarbonPeriod::create($checkIn, $checkOut->copy()->subDay()) as $date) {
            $periodDates[] = $date->format('Y-m-d');
        }

        // 1. TRANSAKSI: baca total fisik DI DALAM transaksi + kunci stok atomik.
        $reservation = DB::transaction(function () use ($validated, $user, $roomType, $totalAmount, $periodDates, $requestedRooms) {
            $totalPhysicalRooms = $roomType->rooms()->where('status', '!=', 'MAINTENANCE')->count();

            if ($totalPhysicalRooms <= 0) {
                throw ValidationException::withMessages([
                    'check_in_date' => 'Maaf, belum ada unit kamar fisik yang tersedia untuk tipe ini.'
                ]);
            }

            foreach ($periodDates as $dateString) {
                $inventory = $this->lockInventoryRow($roomType->id, $dateString);

                $available = $totalPhysicalRooms - $inventory->booked_count;

                if ($available < $requestedRooms) {
                    throw ValidationException::withMessages([
                        'check_in_date' => "Kamar penuh pada tanggal {$dateString}. Sisa ketersediaan: {$available} unit."
                    ]);
                }
            }

            // B. Buat Parent Reservation (kode unik dengan retry).
            $reservationCode = $this->generateUniqueReservationCode();
            $reservation = Reservation::create([
                'reservation_code' => $reservationCode,
                'user_id' => $user->id,
                'guest_name' => $user->name,
                'guest_email' => $user->email,
                'guest_phone' => $user->phone_number ?: '-',
                'reservation_type' => 'ROOM',
                'total_amount' => $totalAmount,
                'status' => 'PENDING_PAYMENT',
            ]);

            // C. Buat Child RoomBooking
            RoomBooking::create([
                'reservation_id' => $reservation->id,
                'room_type_id' => $roomType->id,
                'check_in_date' => $validated['check_in_date'],
                'check_out_date' => $validated['check_out_date'],
                'number_of_rooms' => $requestedRooms,
                'special_requests' => $validated['special_requests'] ?? null,
            ]);

            // D. Kunci Stok Harian Sementara saat Checkout (hold 24 jam)
            foreach ($periodDates as $dateString) {
                $this->lockInventoryRow($roomType->id, $dateString)
                    ->increment('booked_count', $requestedRooms);
            }

            return $reservation;
        });

        // 2. Generate Midtrans Snap Token DI LUAR transaksi agar lock tidak ditahan
        // saat network call lambat. Jika gagal, batalkan + release stok (kompensasi).
        try {
            [$orderId, $snapToken] = $midtransService->createSnapToken($reservation, $totalAmount, 'FULL');
        } catch (Throwable $e) {
            DB::transaction(function () use ($reservation) {
                $fresh = Reservation::whereKey($reservation->id)->lockForUpdate()->first();
                if ($fresh && $fresh->status === 'PENDING_PAYMENT') {
                    $fresh->update(['status' => 'CANCELLED']);
                    $fresh->releaseStock();
                }
            });
            report($e);

            return back()->withErrors(['check_in_date' => 'Gagal membuat pembayaran, silakan coba lagi.']);
        }

        Payment::create([
            'reservation_id' => $reservation->id,
            'transaction_id' => $orderId,
            'payment_type' => 'FULL',
            'amount' => $totalAmount,
            'payment_gateway' => 'MIDTRANS',
            'snap_token' => $snapToken,
            'status' => 'PENDING',
        ]);

        return redirect()->route('booking.pay', $reservation->reservation_code);
    }

    /**
     * Halaman Pembayaran Snap Midtrans (PENDING + CHALLENGE agar tidak 404).
     */
    public function showPaymentPage(string $code)
    {
        $reservation = Reservation::where('reservation_code', $code)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $payment = Payment::where('reservation_id', $reservation->id)
            ->whereIn('status', ['PENDING', 'CHALLENGE'])
            ->latest()
            ->firstOrFail();

        return view('booking.pay', compact('reservation', 'payment'));
    }

    /**
     * Proses Checkout Sewa Gedung Acara
     */
    public function checkoutHall(CheckoutHallRequest $request, MidtransService $midtransService)
    {
        $validated = $request->validated();

        $hall = Hall::where('is_active', true)->findOrFail($validated['hall_id']);
        $user = Auth::user();

        // 1. Hitung Biaya (paket sudah dipastikan milik hall via FormRequest)
        $totalAmount = $hall->base_rental_price;
        if (!empty($validated['event_package_id'])) {
            $package = EventPackage::whereKey($validated['event_package_id'])
                ->where('hall_id', $hall->id)
                ->firstOrFail();
            $totalAmount += $package->price;
        }

        // 2. TRANSAKSI: cek ketersediaan + insert dilakukan atomik.
        $reservation = DB::transaction(function () use ($validated, $user, $hall, $totalAmount) {
            $alreadyBooked = HallAvailability::where([
                ['hall_id', '=', $hall->id],
                ['session_id', '=', $validated['session_id']],
                ['event_date', '=', $validated['event_date']],
            ])->lockForUpdate()->exists();

            if ($alreadyBooked) {
                throw ValidationException::withMessages([
                    'event_date' => 'Gedung tidak tersedia pada tanggal dan sesi terpilih.'
                ]);
            }

            $reservationCode = $this->generateUniqueReservationCode();
            $reservation = Reservation::create([
                'reservation_code' => $reservationCode,
                'user_id' => $user->id,
                'guest_name' => $user->name,
                'guest_email' => $user->email,
                'guest_phone' => $user->phone_number ?: '-',
                'reservation_type' => 'HALL',
                'total_amount' => $totalAmount,
                'status' => 'PENDING_PAYMENT',
            ]);

            HallBooking::create([
                'reservation_id' => $reservation->id,
                'hall_id' => $hall->id,
                'event_package_id' => $validated['event_package_id'] ?? null,
                'session_id' => $validated['session_id'],
                'event_date' => $validated['event_date'],
                'event_type' => $validated['event_type'],
                'special_notes' => $validated['special_notes'] ?? null,
            ]);

            try {
                HallAvailability::create([
                    'hall_id' => $hall->id,
                    'session_id' => $validated['session_id'],
                    'event_date' => $validated['event_date'],
                    'reservation_id' => $reservation->id,
                    'status' => 'LOCKED',
                ]);
            } catch (QueryException $e) {
                // Kalah race terhadap request lain pada unique [hall_id, session_id, event_date]
                if (($e->errorInfo[1] ?? null) == 1062) {
                    throw ValidationException::withMessages([
                        'event_date' => 'Gedung tidak tersedia pada tanggal dan sesi terpilih.'
                    ]);
                }

                throw $e;
            }

            return $reservation;
        });

        try {
            [$orderId, $snapToken] = $midtransService->createSnapToken($reservation, $totalAmount, 'FULL');
        } catch (Throwable $e) {
            DB::transaction(function () use ($reservation) {
                $fresh = Reservation::whereKey($reservation->id)->lockForUpdate()->first();
                if ($fresh && $fresh->status === 'PENDING_PAYMENT') {
                    $fresh->update(['status' => 'CANCELLED']);
                    $fresh->releaseStock();
                }
            });
            report($e);

            return back()->withErrors(['event_date' => 'Gagal membuat pembayaran, silakan coba lagi.']);
        }

        Payment::create([
            'reservation_id' => $reservation->id,
            'transaction_id' => $orderId,
            'payment_type' => 'FULL',
            'amount' => $totalAmount,
            'payment_gateway' => 'MIDTRANS',
            'snap_token' => $snapToken,
            'status' => 'PENDING',
        ]);

        return redirect()->route('booking.pay', $reservation->reservation_code);
    }

    private function generateUniqueReservationCode(): string
    {
        for ($i = 0; $i < 5; $i++) {
            $code = 'HTL-'.date('Ymd').'-'.strtoupper(Str::random(5));
            if (! Reservation::where('reservation_code', $code)->exists()) {
                return $code;
            }
        }

        return 'HTL-'.date('Ymd').'-'.strtoupper(Str::random(8));
    }

    /**
     * Dashboard Riwayat Reservasi Pelanggan.
     * Polling dibatasi max 5 PENDING terbaru per load agar tidak N+1 API + rate-limit.
     * Rekonsiliasi penuh tetap via webhook + scheduler 24 jam.
     */
    public function userReservations()
    {
        $reservations = Reservation::with(['roomBooking.roomType', 'hallBooking.hall', 'payments'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production', false);

        $pendingToSync = $reservations->where('status', 'PENDING_PAYMENT')->take(5);

        foreach ($pendingToSync as $res) {
            $payment = $res->payments->whereIn('status', ['PENDING', 'CHALLENGE'])->first();
            if (!$payment || !$payment->transaction_id) {
                continue;
            }

            try {
                $midtransStatus = Transaction::status($payment->transaction_id);

                // Response API Midtrans berupa stdClass, bukan array.
                $status = $midtransStatus->transaction_status ?? null;
                $fraud = $midtransStatus->fraud_status ?? null;

                if ($status === 'settlement' || ($status === 'capture' && $fraud === 'accept')) {
                    DB::transaction(function () use ($res, $payment, $midtransStatus) {
                        $fresh = Reservation::whereKey($res->id)->lockForUpdate()->first();
                        if (!$fresh || $fresh->status !== 'PENDING_PAYMENT') {
                            return;
                        }

                        $fresh->update(['status' => 'CONFIRMED']);
                        $payment->update([
                            'status' => 'SUCCESS',
                            'payment_method' => $midtransStatus->payment_type ?? 'MIDTRANS',
                            'paid_at' => now(),
                        ]);
                    });
                } elseif ($status === 'capture' && $fraud === 'challenge') {
                    $payment->update(['status' => 'CHALLENGE']);
                } elseif (in_array($status, ['deny', 'expire', 'cancel'])) {
                    DB::transaction(function () use ($res, $payment, $status) {
                        $fresh = Reservation::whereKey($res->id)->lockForUpdate()->first();
                        if (!$fresh || $fresh->status !== 'PENDING_PAYMENT') {
                            return;
                        }

                        $fresh->update(['status' => 'CANCELLED']);
                        // Selaras webhook/command: expire→EXPIRED, lainnya FAILED.
                        $payment->update(['status' => $status === 'expire' ? 'EXPIRED' : 'FAILED']);

                        // Release stok room maupun hall secara idempoten
                        $fresh->releaseStock();
                    });
                }
            } catch (Throwable $e) {
                // Abaikan kegagalan sinkronisasi polling; webhook tetap jalan.
                report($e);
            }
        }

        return view('user.reservations', compact('reservations'));
    }
}
