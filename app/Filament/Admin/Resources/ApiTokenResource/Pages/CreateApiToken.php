<?php

namespace App\Filament\Admin\Resources\ApiTokenResource\Pages;
use App\Filament\Admin\Resources\ApiTokenResource;
use App\Models\ApiToken;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class CreateApiToken extends CreateRecord
{
    protected static string $resource = ApiTokenResource::class;

    // Completely override the create method
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Don't use the default Filament creation process at all
        // Instead use our custom method directly
        $user = auth()->user();
        $plainTextToken = Str::random(64);

        $token = new ApiToken();
        $token->user_id = $user->id;
        $token->name = $data['name'];
        $token->token = hash('sha256', $plainTextToken);
        $token->abilities = $data['abilities'] ?? ['*'];
        $token->expires_at = $data['expires_at'] ?? null;
        $token->save();

        // Store the plain text token for display
        $this->plainTextToken = $plainTextToken;

        return $token;
    }

    protected $plainTextToken;

    protected function afterCreate(): void
    {
        // Display the token to the user - they can only see it once!
        if ($this->plainTextToken) {
            Notification::make()
                ->title('API Token Created')
                ->body(new HtmlString("Your new API token: <strong>{$this->plainTextToken}</strong><br><br>Please copy and save this token as you won't be able to see it again!"))
                ->success()
                ->persistent()
                ->send();
        }
    }

    public function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Token Name')
                    ->placeholder('My API Token')
                    ->helperText('Give your token a descriptive name to remember what it\'s for'),

                CheckboxList::make('abilities')
                    ->options([
                        '*' => 'All abilities (full access)',
                        'read' => 'Read data',
                        'create' => 'Create data',
                        'update' => 'Update data',
                        'delete' => 'Delete data',
                    ])
                    ->default(['*'])
                    ->columns(2)
                    ->label('Token Abilities')
                    ->helperText('Select what this token is allowed to do'),

                DateTimePicker::make('expires_at')
                    ->label('Expiration Date (optional)')
                    ->helperText('Leave empty for a token that never expires'),
            ]);
    }

    // Redirect to the list page after creation
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}