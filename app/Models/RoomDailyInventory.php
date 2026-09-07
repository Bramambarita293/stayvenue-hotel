<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomDailyInventory extends Model
{
    protected $fillable = [
        'room_type_id',
        'date',
        'booked_count',
        'custom_price',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'booked_count' => 'integer',
            'custom_price' => 'decimal:2',
        ];
    }

    public function roomType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }
}
