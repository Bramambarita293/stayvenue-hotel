<?php

namespace App\Filament\Resources\Reservations\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use App\Models\Room;
use App\Models\RoomBooking;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
                        'CHECKED_IN' => 'info',
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
                    ->visible(fn($record) => in_array($record->status, ['CONFIRMED', 'COMPLETED', 'CHECKED_IN', 'CHECKED_OUT'])),

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

                                // Kamar yang tamunya check-out pada tanggal check-in
                                // tetap boleh dipakai (turnover harian normal):
                                // overlap dihitung ketat check_in < baru.check_out
                                // DAN check_out > baru.check_in.
                                return static::availableRoomsQuery($record)
                                    ->pluck('room_number', 'id');
                            })
                            ->required(),
                    ])
                    ->action(function ($record, array $data): void {
                        DB::transaction(function () use ($record, $data): void {
                            $room = Room::whereKey($data['room_id'])
                                ->where('room_type_id', $record->roomBooking->room_type_id)
                                ->lockForUpdate()
                                ->firstOrFail();

                            if ($room->status !== 'AVAILABLE') {
                                throw ValidationException::withMessages([
                                    'room_id' => "Kamar {$room->room_number} sudah tidak tersedia."
                                ]);
                            }

                            $occupied = static::overlappingBookingsExist(
                                $room->id,
                                $record->roomBooking->check_in_date,
                                $record->roomBooking->check_out_date,
                                ignoreRoomBookingId: $record->roomBooking->id,
                            );

                            if ($occupied) {
                                throw ValidationException::withMessages([
                                    'room_id' => "Kamar {$room->room_number} sudah ditempati tamu lain pada rentang tanggal tersebut."
                                ]);
                            }

                            $record->roomBooking->update([
                                'room_id'              => $room->id,
                                'assigned_room_number' => $room->room_number,
                            ]);

                            // 1. Reservasi masuk tahap CHECKED_IN (bukan COMPLETED,
                            //    agar e-voucher & alur check-out tetap konsisten).
                            $record->update(['status' => 'CHECKED_IN']);

                            // 2. Kamar fisik menjadi OCCUPIED.
                            $room->update(['status' => 'OCCUPIED']);
                        });

                        Notification::make()
                            ->title('Check-In Berhasil')
                            ->body("Tamu {$record->guest_name} telah Check-In di Kamar {$record->roomBooking->assigned_room_number}.")
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
                        DB::transaction(function () use ($record): void {
                            $fresh = \App\Models\Reservation::whereKey($record->id)->lockForUpdate()->firstOrFail();
                            $roomBooking = $fresh->roomBooking;

                            if ($roomBooking && $roomBooking->room_id) {
                                $room = \App\Models\Room::whereKey($roomBooking->room_id)->lockForUpdate()->first();
                                if ($room) {
                                    $room->update(['status' => 'CLEANING']);
                                }
                            } elseif ($roomBooking && $roomBooking->assigned_room_number) {
                                $room = \App\Models\Room::where('room_number', $roomBooking->assigned_room_number)->lockForUpdate()->first();
                                if ($room) {
                                    $room->update(['status' => 'CLEANING']);
                                }
                            }

                            $fresh->update(['status' => 'CHECKED_OUT']);
                        });

                        Notification::make()
                            ->title('Check-Out Berhasil')
                            ->body("Kamar telah berhasil di-Check-Out dan sekarang berstatus CLEANING.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn($record) => $record->reservation_type === 'ROOM' && in_array($record->status, ['CHECKED_IN'])),

                Action::make('markRoomClean')
                    ->label('Kamar Bersih')
                    ->icon('heroicon-o-sparkles')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($record): void {
                        DB::transaction(function () use ($record): void {
                            $roomBooking = $record->roomBooking;
                            $room = null;

                            if ($roomBooking?->room_id) {
                                $room = \App\Models\Room::whereKey($roomBooking->room_id)->lockForUpdate()->first();
                            } elseif ($roomBooking?->assigned_room_number) {
                                $room = \App\Models\Room::where('room_number', $roomBooking->assigned_room_number)->lockForUpdate()->first();
                            }

                            if ($room && $room->status === 'CLEANING') {
                                $room->update(['status' => 'AVAILABLE']);
                            }
                        });

                        Notification::make()
                            ->title('Kamar Siap')
                            ->body('Status kamar dikembalikan ke AVAILABLE.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn($record) => $record->reservation_type === 'ROOM' && $record->status === 'CHECKED_OUT'),
            ]);
    }

    /**
     * Kamar fisik AVAILABLE untuk tipe & rentang tanggal reservasi tertentu,
     * mengabaikan booking milik reservasi itu sendiri. Overlap ketat:
     * existing.check_in < new.check_out AND existing.check_out > new.check_in.
     */
    public static function availableRoomsQuery($record)
    {
        $checkIn  = $record->roomBooking->check_in_date;
        $checkOut = $record->roomBooking->check_out_date;
        $typeId   = $record->roomBooking->room_type_id;

        return Room::where('room_type_id', $typeId)
            ->where('status', 'AVAILABLE')
            ->whereDoesntHave('roomBookings', function ($query) use ($checkIn, $checkOut, $record) {
                $query->where('id', '!=', $record->roomBooking->id)
                    ->whereHas('reservation', function ($q) {
                        $q->whereIn('status', ['CONFIRMED', 'CHECKED_IN', 'COMPLETED']);
                    })
                    ->where('check_in_date', '<', $checkOut)
                    ->where('check_out_date', '>', $checkIn);
            });
    }

    /**
     * Re-check overlap saat submit (anti race antar admin).
     */
    public static function overlappingBookingsExist(int $roomId, string $checkIn, string $checkOut, ?int $ignoreRoomBookingId = null): bool
    {
        return RoomBooking::where('room_id', $roomId)
            ->when($ignoreRoomBookingId, fn ($q) => $q->where('id', '!=', $ignoreRoomBookingId))
            ->whereHas('reservation', fn ($q) => $q->whereIn('status', ['CONFIRMED', 'CHECKED_IN', 'COMPLETED']))
            ->where('check_in_date', '<', $checkOut)
            ->where('check_out_date', '>', $checkIn)
            ->exists();
    }
}

