<?php

namespace App\Filament\Resources\HallAvailabilities\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class HallAvailabilitiesTable
{
    public static function configure(Table $table): Table
    {
        $months = DB::table('hall_availabilities')
            ->selectRaw("DISTINCT MONTH(event_date) as month, YEAR(event_date) as year")
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get()
            ->mapWithKeys(fn ($row) => [
                "{$row->year}-{$row->month}" => \Carbon\Carbon::createFromDate($row->year, $row->month)->translatedFormat('F Y'),
            ]);

        return $table
            ->columns([
                TextColumn::make('hall.name')
                    ->label('Gedung')
                    ->searchable(),

                TextColumn::make('session.session_name')
                    ->label('Sesi Waktu'),

                TextColumn::make('event_date')
                    ->label('Tanggal Terkunci')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'LOCKED' => 'danger',
                        'MAINTENANCE' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('event_date')
                    ->label('Periode Bulan')
                    ->options($months)
                    ->query(function ($query, array $data): void {
                        if (!empty($data['value'])) {
                            [$year, $month] = explode('-', $data['value']);
                            $query->whereYear('event_date', $year)
                                ->whereMonth('event_date', $month);
                        }
                    }),
            ]);
    }
}