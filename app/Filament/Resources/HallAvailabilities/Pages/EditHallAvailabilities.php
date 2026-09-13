<?php

namespace App\Filament\Resources\HallAvailabilities\Pages;

use App\Filament\Resources\HallAvailabilities\HallAvailabilityResource;
use App\Models\HallAvailability;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $exists = HallAvailability::where('hall_id', $data['hall_id'])
            ->where('session_id', $data['session_id'])
            ->where('event_date', $data['event_date'])
            ->where('id', '!=', $this->record->id)
            ->exists();

        if ($exists) {
            Notification::make()
                ->title('Duplikat Tanggal')
                ->body('Tanggal, gedung, dan sesi ini sudah dikunci sebelumnya.')
                ->danger()
                ->send();

            $this->halt();
        }

        return $data;
    }
}
