<?php

namespace App\Filament\Resources\Reservations\Pages;

use App\Filament\Resources\Reservations\ReservationResource;
use Filament\Resources\Pages\ListRecords;

class ListReservations extends ListRecords
{
    protected static string $resource = ReservationResource::class;

    protected function getHeaderActions(): array
    {
        // Tanpa tombol create: reservasi hanya lahir dari checkout user.
        return [];
    }

    protected function getTableQuery(): ?\Illuminate\Database\Eloquent\Builder
    {
        return parent::getTableQuery()?->with([
            'roomBooking.roomType',
            'hallBooking.hall',
            'hallBooking.session',
            'payments',
        ]);
    }
}
