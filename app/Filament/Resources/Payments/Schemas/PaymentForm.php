<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Rincian Transaksi Pembayaran')
                    ->schema([
                        Select::make('reservation_id')
                            ->label('Kode Booking')
                            ->relationship('reservation', 'reservation_code')
                            ->disabled()
                            ->required(),

                        TextInput::make('transaction_id')
                            ->label('Midtrans Transaction ID')
                            ->readOnly(),

                        TextInput::make('payment_type')
                            ->label('Tipe Pembayaran')
                            ->readOnly(),

                        TextInput::make('payment_method')
                            ->label('Metode (Gopay/VA/QRIS/CC)')
                            ->readOnly(),

                        TextInput::make('amount')
                            ->label('Jumlah Bayar')
                            ->prefix('Rp')
                            ->readOnly(),

                        TextInput::make('status')
                            ->label('Status Transaksi')
                            ->readOnly(),

                        DateTimePicker::make('paid_at')
                            ->label('Waktu Pembayaran')
                            ->disabled(),
                    ])->columns(2),
            ]);
    }
}