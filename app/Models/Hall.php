<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

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

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function (Hall $hall) {
            if ($hall->hallBookings()->exists()) {
                throw ValidationException::withMessages([
                    'hall' => 'Gedung tidak bisa dihapus karena masih memiliki booking terkait.',
                ]);
            }

            $hall->eventPackages()->delete();
            $hall->hallAvailabilities()->delete();
        });
    }
}
