<?php

namespace App\Filament\Resources\HallAvailabilities\Schemas;

use App\Models\HallAvailability;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\ValidationException;

class HallAvailabilityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Blokir Tanggal / Ketersediaan Gedung')
                    ->schema([
                        Select::make('hall_id')
                            ->label('Gedung / Ballroom')
                            ->relationship('hall', 'name')
                            ->required()
                            ->live(),

                        Select::make('session_id')
                            ->label('Sesi Pemakaian')
                            ->relationship('session', 'session_name')
                            ->required(),

                        DatePicker::make('event_date')
                            ->label('Tanggal')
                            ->required()
                            ->native(false)
                            ->afterStateUpdated(function ($get, ?string $operation, $state) {
                                if ($state && \Carbon\Carbon::parse($state)->isPast()) {
                                    throw ValidationException::withMessages([
                                        'event_date' => 'Tidak bisa memblokir tanggal yang sudah lewat.',
                                    ]);
                                }
                            }),

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