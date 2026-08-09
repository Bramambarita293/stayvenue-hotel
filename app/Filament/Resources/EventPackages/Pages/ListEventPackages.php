<?php

namespace App\Filament\Resources\EventPackages\Pages;

use App\Filament\Resources\EventPackages\EventPackageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEventPackages extends ListRecords
{
    protected static string $resource = EventPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
