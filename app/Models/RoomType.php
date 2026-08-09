<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomType extends Model
{
    protected $guarded = [];

    protected $casts = [
        'images' => 'array',
    ];

    public function roomBookings(): HasMany
    {
        return $this->hasMany(RoomBooking::class);
    }

    public function dailyInventories(): HasMany
    {
        return $this->hasMany(RoomDailyInventory::class);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function getTotalInventoryAttribute(): int
    {
        return $this->rooms()->where('status', '!=', 'MAINTENANCE')->count();
    }
}
