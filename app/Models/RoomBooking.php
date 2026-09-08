<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomBooking extends Model
{
    protected $fillable = [
        'reservation_id',
        'room_type_id',
        'room_id',
        'check_in_date',
        'check_out_date',
        'number_of_rooms',
        'assigned_room_number',
        'special_requests',
    ];

    protected function casts(): array
    {
        return [
            'number_of_rooms' => 'integer',
        ];
    }

    /**
     * Kolom DATE wajib tersimpan format Y-m-d persis (lihat RoomDailyInventory).
     */
    protected function checkInDate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->startOfDay() : null,
            set: fn ($value) => $value ? Carbon::parse($value)->format('Y-m-d') : null,
        );
    }

    protected function checkOutDate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->startOfDay() : null,
            set: fn ($value) => $value ? Carbon::parse($value)->format('Y-m-d') : null,
        );
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}