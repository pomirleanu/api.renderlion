<?php

namespace App\Filament\Admin\Resources\ApiTokenResource\Pages;

use App\Filament\Admin\Resources\ApiTokenResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewApiToken extends ViewRecord
{
    protected static string $resource = ApiTokenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('regenerate')
                ->label('Regenerate Token')
                ->color('warning')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->action(function () {
                    $record = $this->record;
                    $tokenData = \App\Models\ApiToken::createToken(
                        $record->user,
                        $record->name,
                        $record->abilities,
                        $record->expires_at
                    );

                    $record->delete();

                    \Filament\Notifications\Notification::make()
                        ->title('API Token Regenerated')
                        ->body("Your new API token: {$tokenData['plain_text_token']}\n\nPlease copy this token now as you won't be able to see it again!")
                        ->success()
                        ->persistent()
                        ->send();

                    return redirect(ApiTokenResource::getUrl());
                }),
        ];
    }
}