<?php

namespace App\Filament\Resources\RoomResource\Pages;

use App\Filament\Resources\Rooms\RoomResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRoom extends CreateRecord
{
    protected static string $resource = RoomResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}