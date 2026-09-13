<?php

namespace App\Filament\Resources\Reservations\Pages;

use App\Filament\Resources\Reservations\ReservationResource;
use App\Models\Room;
use App\Models\Reservation;
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
        ];
    }

    protected function beforeSave(): void
    {
        $this->originalStatus = $this->getRecord()->getOriginal('status');
        $newStatus = $this->data['status'] ?? $this->getRecord()->status;

        if ($this->originalStatus === $newStatus) {
            return;
        }

        $validTransitions = [
            'PENDING_PAYMENT' => ['CONFIRMED', 'CANCELLED'],
            'CONFIRMED' => ['CHECKED_IN', 'CANCELLED'],
            'CHECKED_IN' => ['CHECKED_OUT', 'CANCELLED'],
            'CHECKED_OUT' => ['COMPLETED'],
            'COMPLETED' => [],
            'CANCELLED' => [],
        ];

        $allowed = $validTransitions[$this->originalStatus] ?? [];

        if (! in_array($newStatus, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => "Transisi dari {$this->originalStatus} ke {$newStatus} tidak diperbolehkan.",
            ]);
        }

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

    protected function afterSave(): void
    {
        $record = $this->getRecord();

        DB::transaction(function () use ($record): void {
            $fresh = Reservation::whereKey($record->id)->lockForUpdate()->first();

            if (! $fresh) {
                return;
            }

            $newStatus = $fresh->status;

            if ($this->originalStatus === 'CONFIRMED' && $newStatus === 'CHECKED_IN') {
                $roomBooking = $fresh->roomBooking;
                if ($roomBooking && $roomBooking->room_id) {
                    $room = Room::whereKey($roomBooking->room_id)->lockForUpdate()->first();
                    if ($room && $room->status === 'AVAILABLE') {
                        $room->update(['status' => 'OCCUPIED']);
                    }
                }
            }

            if ($this->originalStatus === 'CHECKED_IN' && $newStatus === 'CHECKED_OUT') {
                $roomBooking = $fresh->roomBooking;
                if ($roomBooking && $roomBooking->room_id) {
                    $room = Room::whereKey($roomBooking->room_id)->lockForUpdate()->first();
                    if ($room && $room->status === 'OCCUPIED') {
                        $room->update(['status' => 'CLEANING']);
                    }
                }
            }

            if ($newStatus === 'CANCELLED' && $this->originalStatus !== 'CANCELLED') {
                $fresh->payments()->whereIn('status', ['PENDING', 'CHALLENGE'])->update(['status' => 'EXPIRED']);

                $roomBooking = $fresh->roomBooking;
                if ($roomBooking && ($roomBooking->room_id || $roomBooking->assigned_room_number)) {
                    $room = $roomBooking->room_id
                        ? Room::whereKey($roomBooking->room_id)->lockForUpdate()->first()
                        : Room::where('room_number', $roomBooking->assigned_room_number)->lockForUpdate()->first();

                    if ($room && $room->status === 'OCCUPIED') {
                        $room->update(['status' => 'AVAILABLE']);
                    }

                    $roomBooking->update(['room_id' => null, 'assigned_room_number' => null]);
                }

                $fresh->releaseStock();
            }
        });
    }
}
