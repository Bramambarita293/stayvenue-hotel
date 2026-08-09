<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('reservation_id')
                    ->numeric(),
                TextEntry::make('payment_type')
                    ->badge(),
                TextEntry::make('amount')
                    ->numeric(),
                TextEntry::make('payment_gateway'),
                TextEntry::make('transaction_id')
                    ->placeholder('-'),
                TextEntry::make('payment_method')
                    ->placeholder('-'),
                TextEntry::make('status'),
                TextEntry::make('paid_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
