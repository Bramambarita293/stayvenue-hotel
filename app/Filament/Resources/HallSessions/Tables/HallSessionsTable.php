<?php

namespace App\Filament\Resources\HallSessions\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HallSessionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('session_name')
                    ->label('Nama Sesi')
                    ->searchable(),

                TextColumn::make('start_time')
                    ->label('Jam Mulai')
                    ->time('H:i'),

                TextColumn::make('end_time')
                    ->label('Jam Selesai')
                    ->time('H:i'),
            ]);
    }
}