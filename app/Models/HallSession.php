<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HallSession extends Model
{
    protected $fillable = [
        'session_name',
        'start_time',
        'end_time',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
        ];
    }

    public function hallBookings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(HallBooking::class, 'session_id');
    }

    public function hallAvailabilities(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(HallAvailability::class, 'session_id');
    }
}
