<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use BackedEnum;

class SiteSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Pengaturan Situs';

    protected static ?string $title = 'Pengaturan Situs';

    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan';

    public ?array $data = [];

    public function mount(): void
    {
        $this->content->fill([
            'auth_login_cover' => SiteSetting::get(SiteSetting::AUTH_LOGIN_COVER),
            'auth_register_cover' => SiteSetting::get(SiteSetting::AUTH_REGISTER_COVER),
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Cover Login & Register')
                    ->description('Gambar sampul halaman login dan register. Kosongkan untuk memakai foto produk / bawaan.')
                    ->schema([
                        FileUpload::make('auth_login_cover')
                            ->label('Cover Login')
                            ->image()
                            ->disk('s3')
                            ->directory('site')
                            ->maxSize(5120)
                            ->imageEditor()
                            ->columnSpanFull(),
                        FileUpload::make('auth_register_cover')
                            ->label('Cover Register')
                            ->image()
                            ->disk('s3')
                            ->directory('site')
                            ->maxSize(5120)
                            ->imageEditor()
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan')
                ->action('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->content->getState();

        foreach ([
            'auth_login_cover' => SiteSetting::AUTH_LOGIN_COVER,
            'auth_register_cover' => SiteSetting::AUTH_REGISTER_COVER,
        ] as $field => $key) {
            $old = SiteSetting::get($key);
            $new = $data[$field] ?? null;

            if ($old && $new !== $old && Storage::disk('s3')->exists($old)) {
                Storage::disk('s3')->delete($old);
            }

            SiteSetting::set($key, $new);
        }

        Notification::make()
            ->title('Pengaturan disimpan')
            ->success()
            ->send();
    }
}
