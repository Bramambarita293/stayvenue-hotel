<?php

namespace App\Filament\Resources\EventPackages\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EventPackagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('package_name')
                    ->label('Nama Paket')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('hall.name')
                    ->label('Gedung / Ballroom')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Harga Paket')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ]);
    }
}