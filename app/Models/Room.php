<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $guarded = [];

    protected $casts = [
        'room_type_id' => 'integer',
    ];

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
    }

    public function roomBookings(): HasMany
    {
        return $this->hasMany(RoomBooking::class);
    }
}
