<?php

namespace App\Filament\Resources\HallSessions\Pages;

use App\Filament\Resources\HallSessions\HallSessionResource;
use App\Models\HallSession;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditHallSession extends EditRecord
{
    protected static string $resource = HallSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $overlaps = HallSession::where('id', '!=', $this->record->id)
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