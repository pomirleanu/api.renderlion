<?php

namespace App\Filament\Admin\Resources\ApiTokenResource\Pages;

use App\Filament\Admin\Resources\ApiTokenResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateApiToken extends CreateRecord
{
    protected static string $resource = ApiTokenResource::class;
}
