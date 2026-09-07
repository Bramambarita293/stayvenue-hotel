<?php

namespace App\Filament\Resources\ContactMessages\Tables;

use App\Models\ContactMessage;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ContactMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->weight(fn (ContactMessage $record) => $record->is_read ? 'normal' : 'bold'),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->color('gray'),

                TextColumn::make('subject')
                    ->label('Subjek')
                    ->limit(40)
                    ->placeholder('-'),

                TextColumn::make('message')
                    ->label('Isi Pesan')
                    ->limit(60)
                    ->tooltip(fn (ContactMessage $record) => $record->message),

                IconColumn::make('is_read')
                    ->label('Dibaca')
                    ->boolean()
                    ->trueColor('gray')
                    ->falseColor('danger'),

                TextColumn::make('created_at')
                    ->label('Dikirim')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_read')
                    ->label('Status Baca')
                    ->trueLabel('Belum dibaca')
                    ->falseLabel('Sudah dibaca')
                    ->queries(
                        true: fn (Builder $query) => $query->where('is_read', false),
                        false: fn (Builder $query) => $query->where('is_read', true),
                    ),
            ])
            ->recordActions([
                Action::make('markAsRead')
                    ->label('Tandai Dibaca')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn (ContactMessage $record) => $record->update(['is_read' => true]))
                    ->visible(fn (ContactMessage $record) => !$record->is_read)
                    ->requiresConfirmation(false),

                Action::make('markAsUnread')
                    ->label('Tandai Belum Dibaca')
                    ->icon('heroicon-o-envelope')
                    ->color('gray')
                    ->action(fn (ContactMessage $record) => $record->update(['is_read' => false]))
                    ->visible(fn (ContactMessage $record) => $record->is_read)
                    ->requiresConfirmation(false),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
