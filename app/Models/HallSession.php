<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

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

    public function hallBookings(): HasMany
    {
        return $this->hasMany(HallBooking::class, 'session_id');
    }

    public function hallAvailabilities(): HasMany
    {
        return $this->hasMany(HallAvailability::class, 'session_id');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function (HallSession $session) {
            if ($session->hallBookings()->exists()) {
                throw ValidationException::withMessages([
                    'session' => 'Sesi tidak bisa dihapus karena masih memiliki booking terkait.',
                ]);
            }
        });
    }
}
