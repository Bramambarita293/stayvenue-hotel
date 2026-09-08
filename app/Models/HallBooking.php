<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HallBooking extends Model
{
    public const EVENT_TYPE_LABELS = [
        'wedding' => 'Pernikahan',
        'corporate' => 'Rapat / Korporat',
        'birthday' => 'Ulang Tahun',
        'graduation' => 'Wisuda',
        'seminar' => 'Seminar',
        'other' => 'Lainnya',
    ];    protected $fillable = [
        'reservation_id',
        'hall_id',
        'event_package_id',
        'session_id',
        'event_date',
        'event_type',
        'vendor_setup_time',
        'special_notes',
    ];

    protected function casts(): array
    {
        return [
            'vendor_setup_time' => 'datetime',
        ];
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

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function hall(): BelongsTo
    {
        return $this->belongsTo(Hall::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(HallSession::class, 'session_id');
    }

    public function eventPackage(): BelongsTo
    {
        return $this->belongsTo(EventPackage::class);
    }

    public function getEventTypeLabelAttribute(): string
    {
        return self::EVENT_TYPE_LABELS[$this->event_type] ?? (string) $this->event_type;
    }
}