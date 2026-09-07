<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Filament\Resources\Payments\PaymentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPayment extends ViewRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Status payment hanya boleh berubah lewat webhook/polling Midtrans,
            // bukan diedit manual — route 'edit' tidak terdaftar di resource.
            DeleteAction::make(),
        ];
    }
}
