<?php

namespace App\Filament\Resources\EventPackages\Pages;

use App\Filament\Resources\EventPackages\EventPackageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEventPackage extends EditRecord
{
    protected static string $resource = EventPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
