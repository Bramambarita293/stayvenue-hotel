<?php

namespace App\Filament\Resources\HallSessions\Pages;

use App\Filament\Resources\HallSessions\HallSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHallSession extends EditRecord
{
    protected static string $resource = HallSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}