<?php

namespace App\Filament\Resources\HallAvailabilities\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HallAvailabilitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('hall.name')
                    ->label('Gedung')
                    ->searchable(),

                TextColumn::make('session.session_name')
                    ->label('Sesi Waktu'),

                TextColumn::make('event_date')
                    ->label('Tanggal Terkunci')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'LOCKED' => 'danger',
                        'MAINTENANCE' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ]);
    }
}