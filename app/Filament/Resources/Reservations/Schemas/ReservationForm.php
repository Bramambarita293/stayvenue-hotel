<?php

namespace App\Filament\Resources\Reservations\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class ReservationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Rincian Pemesanan')
                    ->schema([
                        TextInput::make('reservation_code')
                            ->label('Kode Booking')
                            ->readOnly(),

                        TextInput::make('guest_name')
                            ->label('Nama Pemesan')
                            ->required(),

                        TextInput::make('guest_email')
                            ->label('Email')
                            ->email()
                            ->required(),

                        TextInput::make('guest_phone')
                            ->label('No. Telepon')
                            ->required(),

                        TextInput::make('reservation_type')
                            ->label('Kategori')
                            ->readOnly(),

                        TextInput::make('total_amount')
                            ->label('Total Biaya')
                            ->prefix('Rp')
                            ->readOnly(),

                        Select::make('status')
                            ->options([
                                'PENDING_PAYMENT' => 'Pending Payment',
                                'CONFIRMED' => 'Confirmed',
                                'CANCELLED' => 'Cancelled',
                                'COMPLETED' => 'Completed',
                            ])->required(),
                    ])->columns(2),
            ]);
    }
}