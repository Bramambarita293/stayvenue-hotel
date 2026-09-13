<?php

namespace App\Filament\Resources\HallSessions\Pages;

use App\Filament\Resources\HallSessions\HallSessionResource;
use App\Models\HallSession;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateHallSession extends CreateRecord
{
    protected static string $resource = HallSessionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $overlaps = HallSession::where('id', '!=', $this->record?->id ?? 0)
            ->where(function ($query) use ($data) {
                $query->where(function ($q) use ($data) {
                    $q->where('start_time', '<', $data['end_time'])
                        ->where('end_time', '>', $data['start_time']);
                });
            })
            ->exists();

        if ($overlaps) {
            Notification::make()
                ->title('Waktu Tumpang Tindih')
                ->body('Sesi waktu baru tumpang tindih dengan sesi yang sudah ada.')
                ->danger()
                ->send();

            $this->halt();
        }

        return $data;
    }
}