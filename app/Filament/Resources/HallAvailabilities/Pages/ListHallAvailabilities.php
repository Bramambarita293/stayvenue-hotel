<?php

namespace App\Filament\Resources\HallAvailabilities\Pages;

use App\Filament\Resources\HallAvailabilities\HallAvailabilityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHallAvailabilities extends ListRecords
{
    protected static string $resource = HallAvailabilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
