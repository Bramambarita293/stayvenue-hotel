<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hall extends Model
{
    protected $fillable = [
        'name',
        'capacity_pax',
        'base_rental_price',
        'description',
        'is_active',
        'images',
    ];

    protected $casts = [
        'images' => 'array',
        'is_active' => 'boolean',
        'base_rental_price' => 'decimal:2',
        'capacity_pax' => 'integer',
    ];

    public function eventPackages(): HasMany
    {
        return $this->hasMany(EventPackage::class);
    }

    public function hallBookings(): HasMany
    {
        return $this->hasMany(HallBooking::class);
    }

    public function hallAvailabilities(): HasMany
    {
        return $this->hasMany(HallAvailability::class);
    }
}
