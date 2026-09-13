<?php

namespace App\Filament\Resources\HallAvailabilities\Pages;

use App\Filament\Resources\HallAvailabilities\HallAvailabilityResource;
use App\Models\HallAvailability;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateHallAvailabilities extends CreateRecord
{
    protected static string $resource = HallAvailabilityResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $exists = HallAvailability::where('hall_id', $data['hall_id'])
            ->where('session_id', $data['session_id'])
            ->where('event_date', $data['event_date'])
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
