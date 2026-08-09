<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    protected $guarded = [];

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
}
