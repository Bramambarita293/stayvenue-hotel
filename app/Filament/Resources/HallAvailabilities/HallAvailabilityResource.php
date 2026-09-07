<?php

namespace App\Filament\Resources\HallAvailabilities;

use App\Filament\Resources\HallAvailabilities\Pages\CreateHallAvailabilities;
use App\Filament\Resources\HallAvailabilities\Pages\EditHallAvailabilities;
use App\Filament\Resources\HallAvailabilities\Pages\ListHallAvailabilities;
use App\Filament\Resources\HallAvailabilities\Schemas\HallAvailabilityForm;
use App\Filament\Resources\HallAvailabilities\Tables\HallAvailabilitiesTable;
use App\Models\HallAvailability;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

class HallAvailabilityResource extends Resource
{
    protected static ?string $model = HallAvailability::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Manajemen Gedung & Event';

    protected static ?string $navigationLabel = 'Hall Availabilities';

    public static function form(Schema $schema): Schema
    {
        return HallAvailabilityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HallAvailabilitiesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHallAvailabilities::route('/'),
            'create' => CreateHallAvailabilities::route('/create'),
            'edit' => EditHallAvailabilities::route('/{record}/edit'),
        ];
    }
}