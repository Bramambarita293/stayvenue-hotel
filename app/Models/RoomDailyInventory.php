<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
            'booked_count' => 'integer',
            'custom_price' => 'decimal:2',
        ];
    }

    /**
     * Kolom DATE wajib tersimpan format Y-m-d persis. Cast 'date' bawaan
     * menulis 'Y-m-d H:i:s' sehingga lookup Y-m-d meleset di DB ketat
     * (SQLite TEXT; MySQL lolos hanya karena koersi kolom DATE).
     */
    protected function date(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->startOfDay() : null,
            set: fn ($value) => $value ? Carbon::parse($value)->format('Y-m-d') : null,
        );
    }

    public function roomType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }
}
