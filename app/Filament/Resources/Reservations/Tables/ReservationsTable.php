<?php

namespace App\Filament\Resources\Reservations\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use App\Models\Room;
use Filament\Notifications\Notification;

class ReservationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reservation_code')
                    ->label('Kode Booking')
                    ->searchable(),

                TextColumn::make('guest_name')
                    ->label('Tamu')
                    ->searchable(),

                TextColumn::make('reservation_type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'ROOM' => 'info',
                        'HALL' => 'warning',
                    }),

                TextColumn::make('roomBooking.assigned_room_number')
                    ->label('No. Kamar')
                    ->placeholder('-')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('total_amount')
                    ->label('Total Biaya')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'PENDING_PAYMENT' => 'gray',
                        'CONFIRMED' => 'success',
                        'CANCELLED' => 'danger',
                        'COMPLETED' => 'primary',
                        'CHECKED_OUT' => 'warning',
                        default           => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Tanggal Order')
                    ->dateTime(),
            ])->actions([
                Action::make('downloadVoucher')
                    ->label('E-Voucher')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->url(fn($record) => route('voucher.download', $record->reservation_code))
                    ->openUrlInNewTab()
                    ->visible(fn($record) => in_array($record->status, ['CONFIRMED', 'COMPLETED'])),

                Action::make('checkIn')
                    ->label('Check-In')
                    ->icon('heroicon-o-key')
                    ->color('info')
                    ->form([
                        Select::make('room_id')
                            ->label('Pilih Kamar Fisik (Status Available)')
                            ->options(function ($record) {
                                if (!$record->roomBooking) {
                                    return [];
                                }

                                $checkIn  = $record->roomBooking->check_in_date;
                                $checkOut = $record->roomBooking->check_out_date;
                                $typeId   = $record->roomBooking->room_type_id;

                                // Filter kamar fisik yang statusnya AVAILABLE
                                return Room::where('room_type_id', $typeId)
                                    ->where('status', 'AVAILABLE')
                                    ->whereDoesntHave('roomBookings', function ($query) use ($checkIn, $checkOut, $record) {
                                        $query->where('id', '!=', $record->roomBooking->id)
                                            ->whereHas('reservation', function ($q) {
                                                $q->whereIn('status', ['CONFIRMED', 'COMPLETED']);
                                            })
                                            ->where(function ($q) use ($checkIn, $checkOut) {
                                                $q->whereBetween('check_in_date', [$checkIn, $checkOut])
                                                    ->orWhereBetween('check_out_date', [$checkIn, $checkOut])
                                                    ->orWhere(function ($sub) use ($checkIn, $checkOut) {
                                                        $sub->where('check_in_date', '<=', $checkIn)
                                                            ->where('check_out_date', '>=', $checkOut);
                                                    });
                                            });
                                    })
                                    ->pluck('room_number', 'id');
                            })
                            ->required(),
                    ])
                    ->action(function ($record, array $data): void {
                        $room = Room::findOrFail($data['room_id']);

                        if ($record->roomBooking) {
                            $record->roomBooking->update([
                                'room_id'              => $room->id,
                                'assigned_room_number' => $room->room_number,
                            ]);
                        }

                        // 1. Ubah status reservasi menjadi COMPLETED (Sudah Check-In)
                        $record->update(['status' => 'COMPLETED']);

                        // 2. Ubah status kamar fisik menjadi OCCUPIED (Terisi)
                        $room->update(['status' => 'OCCUPIED']);

                        Notification::make()
                            ->title('Check-In Berhasil')
                            ->body("Tamu {$record->guest_name} telah Check-In di Kamar {$room->room_number}.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn($record) => $record->reservation_type === 'ROOM' && $record->status === 'CONFIRMED'),

                Action::make('checkOut')
                    ->label('Check-Out')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function ($record): void {
                        $roomBooking = $record->roomBooking;

                        if ($roomBooking && $roomBooking->room_id) {
                            $room = Room::find($roomBooking->room_id);
                            if ($room) {
                                $room->update(['status' => 'CLEANING']);
                            }
                        } elseif ($roomBooking && $roomBooking->assigned_room_number) {
                            $room = Room::where('room_number', $roomBooking->assigned_room_number)->first();
                            if ($room) {
                                $room->update(['status' => 'CLEANING']);
                            }
                        }

                        $record->update(['status' => 'CHECKED_OUT']);

                        Notification::make()
                            ->title('Check-Out Berhasil')
                            ->body("Kamar telah berhasil di-Check-Out dan sekarang berstatus CLEANING.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn($record) => $record->reservation_type === 'ROOM' && $record->status === 'COMPLETED'),
            ]);
    }
}
