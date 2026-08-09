<?php

namespace App\Filament\Resources\RoomResource\Pages;

use App\Filament\Resources\Rooms\RoomResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRooms extends ListRecords
{
    protected static string $resource = RoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Kamar Fisik'),
        ];
    }
}