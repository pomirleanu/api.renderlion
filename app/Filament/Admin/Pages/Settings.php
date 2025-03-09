<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;

class Settings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog';

    protected static string $view = 'filament.admin.pages.settings';

    public static function getNavigationLabel(): string
    {
        return 'Api Settings';
    }

    public function getHeading(): string
    {
        return 'Api Settings';
    }

    public function mount(): void
    {
        $data['current_url_api'] = env('APP_URL'.'/api/v1');

        $this->form->fill($data);
    }


}
