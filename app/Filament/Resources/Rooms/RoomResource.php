<?php

namespace App\Filament\Resources\Rooms;

use UnitEnum;
use BackedEnum;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;

use App\Models\Room;
use App\Filament\Resources\RoomResource\Pages\ListRooms;
use App\Filament\Resources\RoomResource\Pages\CreateRoom;
use App\Filament\Resources\RoomResource\Pages\EditRoom;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-check-badge';
    
    // Kelompokkan di menu sidebar
    protected static string|UnitEnum|null $navigationGroup = 'Manajemen Kamar';
    protected static ?string $navigationLabel = 'Room Status';
    protected static ?string $pluralLabel = 'List Rooms';
    protected static ?string $modelLabel = 'List Rooms';

    public static function form(Schema $schema): Schema
    {
        return $schema ->schema([
                TextInput::make('room_number')
                    ->label('Nomor Kamar Fisik')
                    ->required()
                    ->unique(ignoreRecord: true),
                
                Select::make('room_type_id')
                    ->label('Tipe Kamar')
                    ->relationship('roomType', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('status')
                    ->label('Status Kamar')
                    ->options([
                        'AVAILABLE' => 'Available (Tersedia)',
                        'OCCUPIED' => 'Occupied (Terisi)',
                        'CLEANING' => 'Cleaning (Sedang Dibersihkan)',
                        'MAINTENANCE' => 'Maintenance (Perbaikan)',
                    ])
                    ->default('AVAILABLE')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('room_number')
                    ->label('No. Kamar')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->size('lg'),

                TextColumn::make('roomType.name')
                    ->label('Tipe Kamar')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status Saat Ini')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'AVAILABLE' => 'success',
                        'OCCUPIED' => 'danger',
                        'CLEANING' => 'warning',
                        'MAINTENANCE' => 'gray',
                        default => 'gray'
                    }),

                TextColumn::make('updated_at')
                    ->label('Update Terakhir')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Saring Berdasarkan Status')
                    ->options([
                        'AVAILABLE' => 'Tersedia',
                        'OCCUPIED' => 'Terisi',
                        'CLEANING' => 'Butuh Dibersihkan',
                        'MAINTENANCE' => 'Perbaikan',
                    ]),
            ])
            ->actions([
                Action::make('markAsAvailable')
                    ->label('Selesai Dibersihkan')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->action(function (Room $record) {
                        $record->update(['status' => 'AVAILABLE']);

                        Notification::make()
                            ->title('Kamar Siap Digunakan')
                            ->body("Kamar {$record->room_number} telah selesai dibersihkan dan berstatus AVAILABLE.")
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Kamar Selesai Dibersihkan?')
                    ->modalDescription('Apakah Anda yakin kamar ini sudah bersih dan siap digunakan oleh tamu selanjutnya?')
                    ->modalSubmitActionLabel('Ya, Siap Digunakan')
                    ->visible(fn (Room $record) => $record->status === 'CLEANING'),

                // AKSI: Masuk Perbaikan / Maintenance
                Action::make('markAsMaintenance')
                    ->label('Perbaikan')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->color('gray')
                    ->action(function (Room $record) {
                        $record->update(['status' => 'MAINTENANCE']);

                        Notification::make()
                            ->title('Status Perbaikan Aktif')
                            ->body("Kamar {$record->room_number} masuk status MAINTENANCE. Total stok otomatis disesuaikan.")
                            ->warning()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Masuk Mode Perbaikan?')
                    ->modalDescription('Kamar berstatus MAINTENANCE akan secara otomatis mengurangi kapasitas kuota total pemesanan.')
                    ->visible(fn (Room $record) => in_array($record->status, ['AVAILABLE', 'CLEANING'])),

                EditAction::make()->iconButton(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('room_number', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRooms::route('/'),
            'create' => CreateRoom::route('/create'),
            'edit' => EditRoom::route('/{record}/edit'),
        ];
    }
}