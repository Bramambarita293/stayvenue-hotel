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
            report($e);
            $sqlState = $e->errorInfo[0] ?? null;
            if ($sqlState === '23000' || ($e->errorInfo[1] ?? null) == 1062) {
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

        $user = Auth::user();

        $checkIn = Carbon::parse($validated['check_in_date']);
        $checkOut = Carbon::parse($validated['check_out_date']);
        $requestedRooms = (int) $validated['number_of_rooms'];

        $periodDates = [];
        foreach (CarbonPeriod::create($checkIn, $checkOut->copy()->subDay()) as $date) {
            $periodDates[] = $date->format('Y-m-d');
        }

        // 1. TRANSAKSI: harga + total fisik dibaca DI DALAM transaksi (lock baris
        // tipe) + kunci stok atomik. Termasuk anti double-submit: reservasi
        // PENDING identik <10 mnt dipakai ulang.
        $result = DB::transaction(function () use ($validated, $user, $periodDates, $requestedRooms, $checkIn, $checkOut) {
            $duplicate = Reservation::where('user_id', $user->id)
                ->where('reservation_type', 'ROOM')
                ->where('status', 'PENDING_PAYMENT')
                ->where('created_at', '>', now()->subMinutes(10))
                ->whereHas('roomBooking', fn ($q) => $q
                    ->where('room_type_id', $validated['room_type_id'])
                    ->where('check_in_date', $validated['check_in_date'])
                    ->where('check_out_date', $validated['check_out_date'])
                    ->where('number_of_rooms', $requestedRooms))
                ->latest()
                ->first();

            if ($duplicate) {
                return ['reservation' => $duplicate, 'reused' => true, 'totalAmount' => (float) $duplicate->total_amount];
            }

            $roomType = RoomType::whereKey($validated['room_type_id'])->lockForUpdate()->firstOrFail();
            $nights = $checkIn->diffInDays($checkOut);
            $totalAmount = $roomType->base_price * $nights * $requestedRooms;
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

            // E. Placeholder payment INITIATED di DALAM transaksi agar tak pernah yatim.
            $reservation->payments()->create([
                'payment_type' => 'FULL',
                'amount' => $totalAmount,
                'payment_gateway' => 'MIDTRANS',
                'status' => 'INITIATED',
            ]);

            return ['reservation' => $reservation, 'reused' => false, 'totalAmount' => (float) $totalAmount];
        });

        $reservation = $result['reservation'];
        $totalAmount = $result['totalAmount'];

        // Idempotensi: duplikat yang dipakai ulang sudah punya payment aktif.
        if ($reservation->payments()->whereIn('status', ['PENDING', 'CHALLENGE'])->exists()) {
            return redirect()->route('booking.pay', $reservation->reservation_code);
        }

        $payment = $reservation->payments()->where('status', 'INITIATED')->latest()->firstOrFail();

        // 2. Generate Midtrans Snap Token DI LUAR transaksi agar lock tidak ditahan
        // saat network call lambat. Jika gagal, batalkan + release stok (kompensasi).
        try {
            [$orderId, $snapToken] = $midtransService->createSnapToken($reservation, $totalAmount, 'FULL');
        } catch (Throwable $e) {
            DB::transaction(function () use ($reservation, $payment) {
                $fresh = Reservation::whereKey($reservation->id)->lockForUpdate()->first();
                if ($fresh && $fresh->status === 'PENDING_PAYMENT') {
                    $fresh->update(['status' => 'CANCELLED']);
                    Payment::whereKey($payment->id)->update(['status' => 'FAILED']);
                    $fresh->releaseStock();
                }
            });
            report($e);

            return back()->withErrors(['check_in_date' => 'Gagal membuat pembayaran, silakan coba lagi.']);
        }

        try {
            $payment->update([
                'transaction_id' => $orderId,
                'snap_token' => $snapToken,
                'status' => 'PENDING',
            ]);
        } catch (Throwable $e) {
            DB::transaction(function () use ($reservation, $payment) {
                $fresh = Reservation::whereKey($reservation->id)->lockForUpdate()->first();
                if ($fresh && $fresh->status === 'PENDING_PAYMENT') {
                    $fresh->update(['status' => 'CANCELLED']);
                    Payment::whereKey($payment->id)->update(['status' => 'FAILED']);
                    $fresh->releaseStock();
                }
            });
            report($e);

            return back()->withErrors(['check_in_date' => 'Gagal menyimpan data pembayaran, silakan coba lagi.']);
        }

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
     * Terbitkan ulang Snap token bila token lama basi/expired.
     * Aman: hanya bila order lama sudah mati (expire/cancel/deny) atau
     * belum pernah terbit (INITIATED). Order PENDING aktif tidak diganti
     * agar tak tercipta order ganda yang bisa dibayar terpisah.
     */
    public function refreshPayment(string $code, MidtransService $midtransService)
    {
        $result = DB::transaction(function () use ($code, $midtransService) {
            $reservation = Reservation::where('reservation_code', $code)
                ->where('user_id', Auth::id())
                ->where('status', 'PENDING_PAYMENT')
                ->lockForUpdate()
                ->firstOrFail();

            $hasActivePayment = $reservation->payments()
                ->whereIn('status', ['PENDING', 'CHALLENGE'])
                ->whereNotNull('transaction_id')
                ->exists();

            if ($hasActivePayment) {
                return ['status' => 'active'];
            }

            $payment = $reservation->payments()
                ->whereIn('status', ['PENDING', 'CHALLENGE', 'INITIATED'])
                ->latest()
                ->firstOrFail();

            if ($payment->transaction_id) {
                try {
                    $status = Transaction::status($payment->transaction_id);
                    $txnStatus = $status->transaction_status ?? null;

                    if (! in_array($txnStatus, ['expire', 'cancel', 'deny'])) {
                        return ['status' => 'active'];
                    }

                    $payment->update(['status' => $txnStatus === 'expire' ? 'EXPIRED' : 'FAILED']);
                } catch (Throwable $e) {
                    report($e);

                    return ['status' => 'error'];
                }
            }

            try {
                [$orderId, $snapToken] = $midtransService->createSnapToken($reservation, (float) $reservation->total_amount, 'FULL');
            } catch (Throwable $e) {
                report($e);

                return ['status' => 'error'];
            }

            $reservation->payments()->create([
                'transaction_id' => $orderId,
                'payment_type' => 'FULL',
                'amount' => $reservation->total_amount,
                'payment_gateway' => 'MIDTRANS',
                'snap_token' => $snapToken,
                'status' => 'PENDING',
            ]);

            return ['status' => 'ok', 'reservation' => $reservation];
        });

        if ($result['status'] === 'active') {
            return back()->with('error', 'Pembayaran sebelumnya masih aktif. Selesaikan pembayaran atau tunggu hingga kedaluwarsa.');
        }

        if ($result['status'] === 'error') {
            return back()->withErrors(['payment' => 'Gagal membuat pembayaran baru. Coba lagi.']);
        }

        return redirect()->route('booking.pay', $result['reservation']->reservation_code)
            ->with('success', 'Link pembayaran baru telah dibuat.');
    }

    /**
     * Proses Checkout Sewa Gedung Acara
     */
    public function checkoutHall(CheckoutHallRequest $request, MidtransService $midtransService)
    {
        $validated = $request->validated();

        $user = Auth::user();

        // 2. TRANSAKSI: harga + aktivasi dibaca DI DALAM transaksi (lock baris hall),
        // cek ketersediaan + insert atomik. Termasuk anti double-submit.
        $result = DB::transaction(function () use ($validated, $user) {
            $duplicate = Reservation::where('user_id', $user->id)
                ->where('reservation_type', 'HALL')
                ->where('status', 'PENDING_PAYMENT')
                ->where('created_at', '>', now()->subMinutes(10))
                ->whereHas('hallBooking', fn ($q) => $q
                    ->where('hall_id', $validated['hall_id'])
                    ->where('session_id', $validated['session_id'])
                    ->where('event_date', $validated['event_date']))
                ->latest()
                ->first();

            if ($duplicate) {
                return ['reservation' => $duplicate, 'totalAmount' => (float) $duplicate->total_amount];
            }

            // Kunci baris hall: cegah perubahan harga/deaktivasi di tengah checkout.
            $hall = Hall::whereKey($validated['hall_id'])->lockForUpdate()->firstOrFail();
            if (! $hall->is_active) {
                throw ValidationException::withMessages([
                    'hall_id' => 'Gedung tidak tersedia saat ini.',
                ]);
            }

            // 1. Hitung Biaya (paket sudah dipastikan milik hall via FormRequest)
            $totalAmount = $hall->base_rental_price;
            if (!empty($validated['event_package_id'])) {
                $package = EventPackage::whereKey($validated['event_package_id'])
                    ->where('hall_id', $hall->id)
                    ->lockForUpdate()
                    ->firstOrFail();
                $totalAmount += $package->price;
            }

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
                // Kalah race terhadap request lain pada unique [hall_id, session_id, event_date].
                // SQLSTATE 23000 lintas driver (MySQL 1062, pgsql 23505, sqlite 19).
                $sqlState = $e->errorInfo[0] ?? null;
                if ($sqlState === '23000' || ($e->errorInfo[1] ?? null) == 1062) {
                    throw ValidationException::withMessages([
                        'event_date' => 'Gedung tidak tersedia pada tanggal dan sesi terpilih.'
                    ]);
                }

                throw $e;
            }

            // E. Placeholder payment INITIATED di DALAM transaksi agar tak pernah yatim.
            $reservation->payments()->create([
                'payment_type' => 'FULL',
                'amount' => $totalAmount,
                'payment_gateway' => 'MIDTRANS',
                'status' => 'INITIATED',
            ]);

            return ['reservation' => $reservation, 'totalAmount' => (float) $totalAmount];
        });

        $reservation = $result['reservation'];
        $totalAmount = $result['totalAmount'];

        // Idempotensi: duplikat yang dipakai ulang sudah punya payment aktif.
        if ($reservation->payments()->whereIn('status', ['PENDING', 'CHALLENGE'])->exists()) {
            return redirect()->route('booking.pay', $reservation->reservation_code);
        }

        $payment = $reservation->payments()->where('status', 'INITIATED')->latest()->firstOrFail();

        try {
            [$orderId, $snapToken] = $midtransService->createSnapToken($reservation, $totalAmount, 'FULL');
        } catch (Throwable $e) {
            DB::transaction(function () use ($reservation, $payment) {
                $fresh = Reservation::whereKey($reservation->id)->lockForUpdate()->first();
                if ($fresh && $fresh->status === 'PENDING_PAYMENT') {
                    $fresh->update(['status' => 'CANCELLED']);
                    Payment::whereKey($payment->id)->update(['status' => 'FAILED']);
                    $fresh->releaseStock();
                }
            });
            report($e);

            return back()->withErrors(['event_date' => 'Gagal membuat pembayaran, silakan coba lagi.']);
        }

        try {
            $payment->update([
                'transaction_id' => $orderId,
                'snap_token' => $snapToken,
                'status' => 'PENDING',
            ]);
        } catch (Throwable $e) {
            DB::transaction(function () use ($reservation, $payment) {
                $fresh = Reservation::whereKey($reservation->id)->lockForUpdate()->first();
                if ($fresh && $fresh->status === 'PENDING_PAYMENT') {
                    $fresh->update(['status' => 'CANCELLED']);
                    Payment::whereKey($payment->id)->update(['status' => 'FAILED']);
                    $fresh->releaseStock();
                }
            });
            report($e);

            return back()->withErrors(['event_date' => 'Gagal menyimpan data pembayaran, silakan coba lagi.']);
        }

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
                        if (! $fresh || $fresh->status !== 'PENDING_PAYMENT') {
                            return;
                        }

                        $lockedPayment = Payment::whereKey($payment->id)->lockForUpdate()->first();
                        if (! $lockedPayment || ! in_array($lockedPayment->status, ['PENDING', 'CHALLENGE'])) {
                            return;
                        }

                        $fresh->update(['status' => 'CONFIRMED']);
                        $lockedPayment->update([
                            'status' => 'SUCCESS',
                            'payment_method' => $midtransStatus->payment_type ?? 'MIDTRANS',
                            'paid_at' => now(),
                        ]);
                    });
                } elseif ($status === 'capture' && $fraud === 'challenge') {
                    // Terkunci + guard agar tak menimpa SUCCESS dari webhook yang lebih dulu.
                    DB::transaction(function () use ($res, $payment) {
                        $fresh = Reservation::whereKey($res->id)->lockForUpdate()->first();
                        if (! $fresh || $fresh->status !== 'PENDING_PAYMENT') {
                            return;
                        }

                        Payment::whereKey($payment->id)
                            ->whereIn('status', ['PENDING', 'CHALLENGE'])
                            ->update(['status' => 'CHALLENGE']);
                    });
                } elseif (in_array($status, ['deny', 'expire', 'cancel'])) {
                    DB::transaction(function () use ($res, $payment, $status) {
                        $fresh = Reservation::whereKey($res->id)->lockForUpdate()->first();
                        if (! $fresh || $fresh->status !== 'PENDING_PAYMENT') {
                            return;
                        }

                        $lockedPayment = Payment::whereKey($payment->id)->lockForUpdate()->first();
                        if (! $lockedPayment || ! in_array($lockedPayment->status, ['PENDING', 'CHALLENGE'])) {
                            return;
                        }

                        $fresh->update(['status' => 'CANCELLED']);
                        // Selaras webhook/command: expire→EXPIRED, lainnya FAILED.
                        $lockedPayment->update(['status' => $status === 'expire' ? 'EXPIRED' : 'FAILED']);

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
