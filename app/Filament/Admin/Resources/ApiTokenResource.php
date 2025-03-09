<?php

namespace App\Filament\Admin\Resources;
use App\Filament\Admin\Resources\ApiTokenResource\Pages\CreateApiToken;
use App\Filament\Admin\Resources\ApiTokenResource\Pages\ListApiTokens;
use App\Filament\Admin\Resources\ApiTokenResource\Pages\ViewApiToken;
use App\Filament\Resources\ApiTokenResource\Pages;
use App\Models\ApiToken;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ApiTokenResource extends Resource
{
    protected static ?string $model = ApiToken::class;
    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static ?string $navigationGroup = 'User Management';

    // Only allow users to see their own tokens
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', Auth::id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Token Name')
                    ->placeholder('My API Token')
                    ->helperText('Give your token a descriptive name to remember what it\'s for'),

                Forms\Components\CheckboxList::make('abilities')
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

                Forms\Components\DateTimePicker::make('expires_at')
                    ->label('Expiration Date (optional)')
                    ->helperText('Leave empty for a token that never expires'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('abilities')
                    ->badge()
                    ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : $state)
                    ->color(fn ($state) => in_array('*', $state ?? []) ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('last_used_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Never used'),

                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Never expires'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('abilities')
                    ->multiple()
                    ->options([
                        '*' => 'Full Access',
                        'read' => 'Read',
                        'create' => 'Create',
                        'update' => 'Update',
                        'delete' => 'Delete',
                    ]),

                Tables\Filters\Filter::make('expired')
                    ->query(fn ($query) => $query->where('expires_at', '<', now()))
                    ->label('Expired Tokens'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('regenerate')
                    ->label('Regenerate')
                    ->color('warning')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->action(function (ApiToken $record) {
                        $tokenData = ApiToken::createToken(
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
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListApiTokens::route('/'),
            'create' => CreateApiToken::route('/create'),
            'view' => ViewApiToken::route('/{record}'),
        ];
    }
}