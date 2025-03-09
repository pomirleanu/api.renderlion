<?php

namespace App\Filament\Admin\Resources\ApiTokenResource\Pages;

use App\Filament\Admin\Resources\ApiTokenResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;


class ListApiTokens extends ListRecords
{
    protected static string $resource = ApiTokenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Generate New Token'),
        ];
    }
}