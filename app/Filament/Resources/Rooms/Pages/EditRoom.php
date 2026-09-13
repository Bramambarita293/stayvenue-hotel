<?php

namespace App\Filament\Resources\Rooms\Pages;

use App\Filament\Resources\Rooms\RoomResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRoom extends EditRecord
{
    protected static string $resource = RoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalDescription('Kamar yang sedang OCCUPIED tidak bisa dihapus.'),
        ];
    }

    // Redirect kembali ke daftar setelah edit kamar
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}