<?php

namespace App\Filament\Resources\HallSessions;

use App\Filament\Resources\HallSessions\Pages\CreateHallSession;
use App\Filament\Resources\HallSessions\Pages\EditHallSession;
use App\Filament\Resources\HallSessions\Pages\ListHallSessions;
use App\Filament\Resources\HallSessions\Schemas\HallSessionForm;
use App\Filament\Resources\HallSessions\Tables\HallSessionsTable;
use App\Models\HallSession;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class HallSessionResource extends Resource
{
    protected static ?string $model = HallSession::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|UnitEnum|null $navigationGroup = 'Manajemen Gedung & Event';

    protected static ?string $recordTitleAttribute = 'session_name';

    public static function form(Schema $schema): Schema
    {
        return HallSessionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HallSessionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHallSessions::route('/'),
            'create' => CreateHallSession::route('/create'),
            'edit' => EditHallSession::route('/{record}/edit'),
        ];
    }
}