<?php

namespace App\Filament\Resources\RoomTypes\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class RoomTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Tipe Kamar')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Tipe Kamar')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),

                        TextInput::make('slug')
                            ->required()
                            ->readOnly()
                            // Tanpa JS slug bisa kosong: isi dari nama di server.
                            ->dehydrateStateUsing(fn ($state, callable $get) => $state ?: Str::slug((string) $get('name'))),

                        TextInput::make('base_price')
                            ->label('Harga per Malam (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),

                        \Filament\Forms\Components\Placeholder::make('inventory_info')
                            ->label('Total Stok Unit Fisik')
                            ->content('Dihitung otomatis dari tabel Room (di luar MAINTENANCE).'),

                        TextInput::make('max_guests')
                            ->label('Maksimal Tamu')
                            ->numeric()
                            ->default(2)
                            ->required(),

                        FileUpload::make('images')
                            ->label('Galeri Foto Kamar (Slider)')
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->disk('s3')
                            ->directory('room-types')
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Deskripsi & Fasilitas')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}
