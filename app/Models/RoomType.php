<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'base_price',
        'max_guests',
        'images',
    ];

    protected $casts = [
        'images' => 'array',
        'base_price' => 'decimal:2',
        'max_guests' => 'integer',
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
        // Hindari N+1: pakai rooms_count bila sudah di-withCount.
        if (array_key_exists('rooms_count', $this->attributes)) {
            return (int) $this->attributes['rooms_count'];
        }

        if ($this->relationLoaded('rooms')) {
            return $this->rooms->where('status', '!=', 'MAINTENANCE')->count();
        }

        return $this->rooms()->where('status', '!=', 'MAINTENANCE')->count();
    }
}
