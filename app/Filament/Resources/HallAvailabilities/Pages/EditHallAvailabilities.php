<?php

namespace App\Filament\Resources\HallAvailabilities\Pages;

use App\Filament\Resources\HallAvailabilities\HallAvailabilityResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHallAvailabilities extends EditRecord
{
    protected static string $resource = HallAvailabilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
