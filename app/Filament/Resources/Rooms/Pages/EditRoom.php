<?php

namespace App\Filament\Resources\RoomResource\Pages;

use App\Filament\Resources\Rooms\RoomResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRoom extends EditRecord
{
    protected static string $resource = RoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // Redirect kembali ke daftar setelah edit kamar
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}