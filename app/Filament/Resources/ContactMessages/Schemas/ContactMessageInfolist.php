<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ContactMessageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('Nama'),
                TextEntry::make('email')
                    ->label('Email')
                    ->copyable(),
                TextEntry::make('phone')
                    ->label('Telepon / WA')
                    ->placeholder('-')
                    ->copyable(),
                TextEntry::make('subject')
                    ->label('Subjek')
                    ->placeholder('-'),
                TextEntry::make('message')
                    ->label('Isi Pesan')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->label('Dikirim Pada')
                    ->dateTime('d M Y H:i'),
            ]);
    }
}
