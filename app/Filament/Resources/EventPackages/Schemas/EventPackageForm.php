<?php

namespace App\Filament\Resources\EventPackages\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;


class EventPackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Paket Event')
                    ->schema([
                        Select::make('hall_id')
                            ->label('Gedung / Ballroom')
                            ->relationship('hall', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('package_name')
                            ->label('Nama Paket')
                            ->placeholder('Contoh: Silver Wedding Package, Wisuda Paket A')
                            ->required(),

                        TextInput::make('price')
                            ->label('Harga Paket')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),

                        Textarea::make('description')
                            ->label('Inklusi & Rincian Paket')
                            ->placeholder('Sebutkan fasilitas seperti: Catering 300 pax, Sound system 5000W, Dekorasi standard, dll.')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}