<?php

namespace App\Filament\Resources\Halls\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Filament\Actions\DeleteAction;

class HallsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('images')
                    ->label('Foto')
                    ->disk('s3')
                    ->stacked()
                    ->limit(1)
                    ->limitedRemainingText(),

                TextColumn::make('name')
                    ->label('Nama Gedung')
                    ->searchable(),

                TextColumn::make('capacity_pax')
                    ->label('Kapasitas')
                    ->suffix(' Pax')
                    ->sortable(),

                TextColumn::make('base_rental_price')
                    ->label('Harga Sewa Base')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('eventPackages_count')
                    ->label('Paket')
                    ->counts('eventPackages')
                    ->sortable(),

                TextColumn::make('hallBookings_count')
                    ->label('Booking')
                    ->counts('hallBookings')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean(),
            ])
            ->actions([
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalDescription('Gedung yang masih memiliki booking tidak bisa dihapus.'),
            ]);
    }
}
