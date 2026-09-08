<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HallAvailability extends Model
{
    protected $fillable = [
        'hall_id',
        'session_id',
        'event_date',
        'status',
        'reservation_id',
    ];

    protected function casts(): array
    {
        return [];
    }

    /**
     * Kolom DATE wajib tersimpan format Y-m-d persis (lihat RoomDailyInventory).
     */
    protected function eventDate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->startOfDay() : null,
            set: fn ($value) => $value ? Carbon::parse($value)->format('Y-m-d') : null,
        );
    }

    public function hall(): BelongsTo
    {
        return $this->belongsTo(Hall::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(HallSession::class, 'session_id');
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }
}