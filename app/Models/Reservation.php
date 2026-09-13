<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    protected $fillable = [
        'reservation_code',
        'user_id',
        'guest_name',
        'guest_email',
        'guest_phone',
        'reservation_type',
        'total_amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'stock_released_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function roomBooking(): HasOne
    {
        return $this->hasOne(RoomBooking::class);
    }

    public function hallBooking(): HasOne
    {
        return $this->hasOne(HallBooking::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Lepaskan kembali stok yang dikunci oleh reservasi ini.
     *
     * Panggil HANYA di dalam DB::transaction dengan baris reservasi di-lock.
     * Idempoten seumur hidup via stock_released_at: pemanggilan kedua dst
     * (webhook retry, admin cancel, cron, polling) menjadi no-op + tercatat.
     */
    public function releaseStock(): void
    {
        if ($this->stock_released_at !== null) {
            \Illuminate\Support\Facades\Log::info("releaseStock skip {$this->reservation_code}: sudah release pada {$this->stock_released_at}.");
            return;
        }

        if ($this->reservation_type === 'HALL') {
            HallAvailability::where('reservation_id', $this->id)->delete();
            $this->forceFill(['stock_released_at' => now()])->save();

            return;
        }

        if ($this->reservation_type === 'ROOM' && $this->roomBooking) {
            $roomBooking = $this->roomBooking;

            try {
                $checkIn = Carbon::parse($roomBooking->check_in_date);
                $checkOut = Carbon::parse($roomBooking->check_out_date)->subDay();
            } catch (\Throwable) {
                return;
            }

            if ($checkOut->lt($checkIn)) {
                return;
            }

            $dates = CarbonPeriod::create($checkIn, $checkOut);
            $allReleased = true;

            foreach ($dates as $date) {
                $row = RoomDailyInventory::where('room_type_id', $roomBooking->room_type_id)
                    ->where('date', $date->format('Y-m-d'))
                    ->lockForUpdate()
                    ->first();

                if (! $row) {
                    \Illuminate\Support\Facades\Log::warning("releaseStock skip {$this->reservation_code} {$date->format('Y-m-d')}: inventory tidak ditemukan.");
                    $allReleased = false;
                    continue;
                }

                if ($row->booked_count < $roomBooking->number_of_rooms) {
                    \Illuminate\Support\Facades\Log::warning("releaseStock skip {$this->reservation_code} {$date->format('Y-m-d')}: booked_count tidak cukup ({$row->booked_count} < {$roomBooking->number_of_rooms}).");
                    $allReleased = false;
                    continue;
                }

                $row->decrement('booked_count', $roomBooking->number_of_rooms);
            }

            if ($allReleased) {
                $this->forceFill(['stock_released_at' => now()])->save();
            } else {
                \Illuminate\Support\Facades\Log::alert("releaseStock PARTIAL {$this->reservation_code}: beberapa tanggal gagal dilepas. Perlu manual intervention.");
            }
        }
    }
}
