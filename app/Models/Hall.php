<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hall extends Model
{
    protected $guarded = [];

    protected $casts = [
        'images' => 'array',
    ];

    public function eventPackages(): HasMany
    {
        return $this->hasMany(EventPackage::class);
    }

    public function hallBookings(): HasMany
    {
        return $this->hasMany(HallBooking::class);
    }
}
