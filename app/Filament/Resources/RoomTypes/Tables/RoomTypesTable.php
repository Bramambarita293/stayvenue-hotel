<?php

namespace App\Filament\Resources\RoomTypes\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;


class RoomTypesTable
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
                    ->label('Tipe Kamar')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('base_price')
                    ->label('Harga Base')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('rooms_count')
                    ->label('Total Unit')
                    ->sortable(),

                TextColumn::make('max_guests')
                    ->label('Kapasitas')
                    ->suffix(' Pax'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ]);
    }
}
