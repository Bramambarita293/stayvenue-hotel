<?php

namespace App\Filament\Resources\Reservations\Pages;

use App\Filament\Resources\Reservations\ReservationResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateReservation extends CreateRecord
{
    protected static string $resource = ReservationResource::class;

    /**
     * Reservasi wajib lahir dari checkout user (agar booking child +
     * kunci inventori + payment terbentuk). Pembuatan manual diblokir.
     */
    protected function beforeCreate(): void
    {
        Notification::make()
            ->title('Diblokir')
            ->body('Reservasi hanya boleh dibuat lewat checkout user agar stok dan pembayaran konsisten.')
            ->danger()
            ->send();

        $this->halt();
    }
}
