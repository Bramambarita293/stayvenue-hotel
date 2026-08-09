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
                            ->readOnly(),

                        TextInput::make('base_price')
                            ->label('Harga per Malam (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),

                        TextInput::make('total_inventory')
                            ->label('Total Stok Unit Fisik')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Dihitung dari Room Status'),

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
                            ->disk('public')
                            ->directory('room-types')
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Deskripsi & Fasilitas')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}
