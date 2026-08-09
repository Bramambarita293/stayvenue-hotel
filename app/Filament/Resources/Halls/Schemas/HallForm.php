<?php

namespace App\Filament\Resources\Halls\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;

class HallForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Gedung / Ballroom')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Gedung/Ruangan')
                            ->required(),

                        TextInput::make('capacity_pax')
                            ->label('Kapasitas Tamu')
                            ->numeric()
                            ->suffix(' Pax')
                            ->required(),

                        TextInput::make('base_rental_price')
                            ->label('Harga Sewa Base')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),

                        Toggle::make('is_active')
                            ->label('Status Aktif / Bisa Disewa')
                            ->default(true),

                        FileUpload::make('images')
                            ->label('Galeri Foto Gedung (Slider)')
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->disk('public')
                            ->directory('halls')
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Deskripsi Gedung & Layanan')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}
