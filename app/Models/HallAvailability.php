<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HallAvailability extends Model
{
    protected $guarded = [];

    public function hall(): BelongsTo
    {
        return $this->belongsTo(Hall::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(HallSession::class, 'session_id');
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }
}