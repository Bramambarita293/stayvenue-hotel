<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HallBooking extends Model
{
    protected $fillable = [
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
            'event_date' => 'date',
            'vendor_setup_time' => 'datetime',
        ];
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
}