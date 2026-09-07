<?php

namespace App\Filament\Resources\HallAvailabilities\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;

class HallAvailabilityForm
{
    public static function configure($schema)
    {
        return $schema
            ->components([
                Section::make('Blokir Tanggal / Ketersediaan Gedung')
                    ->schema([
                        Select::make('hall_id')
                            ->label('Gedung / Ballroom')
                            ->relationship('hall', 'name')
                            ->required(),

                        Select::make('session_id')
                            ->label('Sesi Pemakaian')
                            ->relationship('session', 'session_name')
                            ->required(),

                        DatePicker::make('event_date')
                            ->label('Tanggal Terkunci')
                            ->required(),

                        Select::make('status')
                            ->label('Status Kunci')
                            ->options([
                                'LOCKED' => 'LOCKED (Terkunci oleh Sistem/User)',
                                'MAINTENANCE' => 'MAINTENANCE (Perbaikan/Manual Block)',
                            ])
                            ->default('MAINTENANCE')
                            ->required(),
                    ])->columns(2),
            ]);
    }
}