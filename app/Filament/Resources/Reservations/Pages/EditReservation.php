<?php

namespace App\Filament\Resources\Reservations\Pages;

use App\Filament\Resources\Reservations\ReservationResource;
use App\Models\Reservation;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EditReservation extends EditRecord
{
    protected static string $resource = ReservationResource::class;

    protected ?string $originalStatus = null;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    /**
     * Cegah pengesahan tanpa bayar: masuk ke status lunas dari PENDING_PAYMENT
     * wajib didukung payment SUCCESS. CANCELLED selalu boleh (efek samping
     * di afterSave, kini idempoten via stock_released_at).
     */
    protected function beforeSave(): void
    {
        $this->originalStatus = $this->getRecord()->getOriginal('status');

        $newStatus = $this->data['status'] ?? $this->getRecord()->status;

        if (
            $this->originalStatus === 'PENDING_PAYMENT'
            && in_array($newStatus, ['CONFIRMED', 'COMPLETED', 'CHECKED_IN', 'CHECKED_OUT'], true)
            && ! $this->getRecord()->payments()->where('status', 'SUCCESS')->exists()
        ) {
            throw ValidationException::withMessages([
                'status' => 'Reservasi belum dibayar (tidak ada payment SUCCESS). Pengesahan manual tanpa pembayaran diblokir.',
            ]);
        }
    }

    /**
     * Perubahan status dari form admin harus menimbulkan efek samping yang
     * sama dengan alur webhook/polling — sebelumnya admin bisa set CANCELLED
     * tanpa melepas stok, sehingga tanggal/kamar terkunci selamanya.
     */
    protected function afterSave(): void
    {
        $record = $this->getRecord();

        if ($this->originalStatus === 'CANCELLED' || $record->status !== 'CANCELLED') {
            return;
        }

        DB::transaction(function () use ($record): void {
            $fresh = Reservation::whereKey($record->id)->lockForUpdate()->first();

            if (!$fresh) {
                return;
            }

            $fresh->payments()->whereIn('status', ['PENDING', 'CHALLENGE'])->update(['status' => 'EXPIRED']);

            // Bebaskan kamar fisik bila cancel dari CHECKED_IN/CONFIRMED yang sudah assign.
            $roomBooking = $fresh->roomBooking;
            if ($roomBooking && ($roomBooking->room_id || $roomBooking->assigned_room_number)) {
                $room = $roomBooking->room_id
                    ? \App\Models\Room::whereKey($roomBooking->room_id)->lockForUpdate()->first()
                    : \App\Models\Room::where('room_number', $roomBooking->assigned_room_number)->lockForUpdate()->first();

                if ($room && $room->status === 'OCCUPIED') {
                    $room->update(['status' => 'AVAILABLE']);
                }

                $roomBooking->update(['room_id' => null, 'assigned_room_number' => null]);
            }

            $fresh->releaseStock();
        });
    }
}
