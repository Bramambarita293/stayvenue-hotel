<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function hallBookings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(HallBooking::class);
    }

    public function hall(): BelongsTo
    {
        return $this->belongsTo(Hall::class);
    }
}
