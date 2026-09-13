<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class EventPackage extends Model
{
    protected $fillable = [
        'hall_id',
        'package_name',
        'price',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    public function hallBookings(): HasMany
    {
        return $this->hasMany(HallBooking::class);
    }

    public function hall(): BelongsTo
    {
        return $this->belongsTo(Hall::class);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function (EventPackage $package) {
            if ($package->hallBookings()->exists()) {
                throw ValidationException::withMessages([
                    'package' => 'Paket event tidak bisa dihapus karena masih memiliki booking terkait.',
                ]);
            }
        });
    }
}
