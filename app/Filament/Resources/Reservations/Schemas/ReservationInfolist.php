<?php

namespace App\Filament\Resources\Reservations\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ReservationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('reservation_code'),
                TextEntry::make('user_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('guest_name'),
                TextEntry::make('guest_email'),
                TextEntry::make('guest_phone'),
                TextEntry::make('reservation_type')
                    ->badge(),
                TextEntry::make('total_amount')
                    ->numeric(),
                TextEntry::make('status'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
