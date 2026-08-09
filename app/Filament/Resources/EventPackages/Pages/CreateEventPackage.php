<?php

namespace App\Filament\Resources\EventPackages\Pages;

use App\Filament\Resources\EventPackages\EventPackageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEventPackage extends CreateRecord
{
    protected static string $resource = EventPackageResource::class;
}
