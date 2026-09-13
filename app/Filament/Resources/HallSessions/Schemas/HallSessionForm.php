<?php

namespace App\Filament\Resources\HallSessions\Schemas;

use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Illuminate\Validation\ValidationException;

class HallSessionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Sesi Pemakaian Gedung')
                    ->schema([
                        TextInput::make('session_name')
                            ->label('Nama Sesi')
                            ->placeholder('Contoh: Half Day Morning, Full Day Event')
                            ->required(),

                        TimePicker::make('start_time')
                            ->label('Jam Mulai')
                            ->seconds(false)
                            ->required(),

                        TimePicker::make('end_time')
                            ->label('Jam Selesai')
                            ->seconds(false)
                            ->required()
                            ->afterStateUpdated(function ($set, $get, ?string $operation, $state) {
                                $start = $get('start_time');
                                if ($start && $state && $state <= $start) {
                                    $set('end_time', null);
                                    throw ValidationException::withMessages([
                                        'end_time' => 'Jam selesai harus lebih besar dari jam mulai.',
                                    ]);
                                }
                            }),
                    ])->columns(3),
            ]);
    }
}