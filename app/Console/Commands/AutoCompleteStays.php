<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomBooking;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class AutoCompleteStays extends Command
{
    protected $signature = 'stays:auto-complete';
    protected $description = 'Check-out otomatis reservasi yang masa inap/acaranya sudah lewat (harian 01:00 WIB)';

    public function handle(): int
    {
        $today = today()->format('Y-m-d');
        $yesterday = today()->subDay()->format('Y-m-d');

        $done = ['room_checkout' => 0, 'room_noshow' => 0, 'room_completed' => 0, 'hall_completed' => 0, 'skipped' => 0];

        // 1+2. ROOM CHECKED_IN / CONFIRMED (no-show) yang check_out_date sudah lewat -> CHECKED_OUT + kamar CLEANING.
        $overdueRooms = Reservation::with('roomBooking')
            ->where('reservation_type', 'ROOM')
            ->whereIn('status', ['CHECKED_IN', 'CONFIRMED'])
            ->whereHas('roomBooking', fn ($q) => $q->whereDate('check_out_date', '<', $today))
            ->get();

        foreach ($overdueRooms as $reservation) {
            try {
                $result = $this->autoCheckoutRoom($reservation);
                $done[$result === 'CHECKED_IN' ? 'room_checkout' : 'room_noshow']++;
            } catch (Throwable $e) {
                $done['skipped']++;
                Log::error("stays:auto-complete gagal untuk {$reservation->reservation_code}: {$e->getMessage()}");
                $this->warn("Lewati {$reservation->reservation_code}: {$e->getMessage()}");
            }
        }

        // 3. ROOM CHECKED_OUT yang lewat >1 hari -> COMPLETED (arsip).
        $archivable = Reservation::where('reservation_type', 'ROOM')
            ->where('status', 'CHECKED_OUT')
            ->whereHas('roomBooking', fn ($q) => $q->whereDate('check_out_date', '<', $yesterday))
            ->get();

        foreach ($archivable as $reservation) {
            DB::transaction(function () use ($reservation, &$done) {
                $fresh = Reservation::whereKey($reservation->id)->lockForUpdate()->first();
                if (! $fresh || $fresh->status !== 'CHECKED_OUT') {
                    return;
                }
                $fresh->update(['status' => 'COMPLETED']);
                $done['room_completed']++;
            });
        }

        // 4. HALL CONFIRMED/CHECKED_IN yang event_date sudah lewat -> COMPLETED.
        $overdueHalls = Reservation::where('reservation_type', 'HALL')
            ->whereIn('status', ['CONFIRMED', 'CHECKED_IN'])
            ->whereHas('hallBooking', fn ($q) => $q->whereDate('event_date', '<', $today))
            ->get();

        foreach ($overdueHalls as $reservation) {
            DB::transaction(function () use ($reservation, &$done) {
                $fresh = Reservation::whereKey($reservation->id)->lockForUpdate()->first();
                if (! $fresh || ! in_array($fresh->status, ['CONFIRMED', 'CHECKED_IN'])) {
                    return;
                }
                $fresh->update(['status' => 'COMPLETED']);
                $done['hall_completed']++;
            });
        }

        $this->info("Selesai: {$done['room_checkout']} check-out, {$done['room_noshow']} no-show, ".
            "{$done['room_completed']} arsip kamar, {$done['hall_completed']} arsip gedung, {$done['skipped']} dilewati.");

        return self::SUCCESS;
    }

    /**
     * Cermin logika tombol Check-Out manual: kamar OCCUPIED -> CLEANING,
     * reservasi -> CHECKED_OUT. Mengembalikan status SEMULA (untuk statistik).
     */
    private function autoCheckoutRoom(Reservation $reservation): string
    {
        $originalStatus = $reservation->status;

        DB::transaction(function () use ($reservation) {
            $fresh = Reservation::whereKey($reservation->id)->lockForUpdate()->firstOrFail();

            if (! in_array($fresh->status, ['CHECKED_IN', 'CONFIRMED'])) {
                return;
            }

            $roomBooking = RoomBooking::where('reservation_id', $fresh->id)->first();

            if ($roomBooking && $roomBooking->room_id) {
                $room = Room::whereKey($roomBooking->room_id)->lockForUpdate()->first();
                if ($room && $room->status === 'OCCUPIED') {
                    $room->update(['status' => 'CLEANING']);
                }
            } elseif ($roomBooking && $roomBooking->assigned_room_number) {
                $room = Room::where('room_number', $roomBooking->assigned_room_number)->lockForUpdate()->first();
                if ($room && $room->status === 'OCCUPIED') {
                    $room->update(['status' => 'CLEANING']);
                }
            }

            // Jejak assign dipertahankan (seperti check-out manual); hanya status yang maju.
            $fresh->update(['status' => 'CHECKED_OUT']);
        });

        Log::info("stays:auto-complete: {$reservation->reservation_code} ({$originalStatus}) -> CHECKED_OUT.");

        return $originalStatus;
    }
}
