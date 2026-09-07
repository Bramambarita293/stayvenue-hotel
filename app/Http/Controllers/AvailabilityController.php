<?php

namespace App\Http\Controllers;

use App\Models\Hall;
use App\Models\HallAvailability;
use App\Models\RoomDailyInventory;
use App\Models\RoomType;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    /**
     * Cek ketersediaan kamar per rentang tanggal (read-only, tanpa lock).
     * Kebenaran akhir tetap di BookingController saat checkout.
     */
    public function room(Request $request)
    {
        $validated = $request->validate([
            'room_type_id' => ['required', 'integer', 'exists:room_types,id'],
            'check_in_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date_format:Y-m-d', 'after:check_in_date'],
            'number_of_rooms' => ['sometimes', 'integer', 'min:1', 'max:5'],
        ]);

        $requested = (int) ($validated['number_of_rooms'] ?? 1);

        try {
            $checkIn = Carbon::parse($validated['check_in_date']);
            $checkOut = Carbon::parse($validated['check_out_date']);
        } catch (\Throwable) {
            return response()->json(['available' => false, 'message' => 'Format tanggal tidak valid.'], 422);
        }

        $nights = $checkIn->diffInDays($checkOut);
        if ($nights < 1 || $nights > 14) {
            return response()->json([
                'available' => false,
                'message' => 'Lama menginap maksimal 14 malam.',
            ]);
        }

        $roomType = RoomType::findOrFail($validated['room_type_id']);
        $total = $roomType->rooms()->where('status', '!=', 'MAINTENANCE')->count();

        if ($total <= 0) {
            return response()->json([
                'available' => false,
                'remaining' => 0,
                'total' => 0,
                'message' => 'Tipe kamar ini sedang penuh.',
            ]);
        }

        $dates = [];
        foreach (CarbonPeriod::create($checkIn, $checkOut->copy()->subDay()) as $date) {
            $dates[] = $date->format('Y-m-d');
        }

        $inventories = RoomDailyInventory::where('room_type_id', $roomType->id)
            ->whereIn('date', $dates)
            ->get()
            ->keyBy(fn ($row) => Carbon::parse($row->date)->format('Y-m-d'));

        $remaining = $total;
        foreach ($dates as $d) {
            $booked = $inventories->get($d)?->booked_count ?? 0;
            $remaining = min($remaining, $total - $booked);
        }

        if ($remaining < $requested) {
            return response()->json([
                'available' => false,
                'remaining' => max(0, $remaining),
                'total' => $total,
                'message' => $remaining <= 0
                    ? 'Penuh pada tanggal ini.'
                    : "Sisa {$remaining} unit pada tanggal ini.",
            ]);
        }

        return response()->json([
            'available' => true,
            'remaining' => $remaining,
            'total' => $total,
            'message' => "Tersedia {$remaining} unit.",
        ]);
    }

    /**
     * Cek ketersediaan gedung per tanggal + sesi (read-only).
     */
    public function hall(Request $request)
    {
        $validated = $request->validate([
            'hall_id' => ['required', 'integer', 'exists:halls,id'],
            'event_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'session_id' => ['required', 'integer', 'exists:hall_sessions,id'],
        ]);

        $hall = Hall::where('is_active', true)->find($validated['hall_id']);

        if (! $hall) {
            return response()->json([
                'available' => false,
                'message' => 'Gedung tidak tersedia.',
            ]);
        }

        $taken = HallAvailability::where('hall_id', $hall->id)
            ->where('session_id', $validated['session_id'])
            ->where('event_date', $validated['event_date'])
            ->exists();

        if ($taken) {
            return response()->json([
                'available' => false,
                'message' => 'Sesi terisi — coba tanggal/sesi lain.',
            ]);
        }

        return response()->json([
            'available' => true,
            'message' => 'Sesi tersedia.',
        ]);
    }
}
