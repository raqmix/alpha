<?php

namespace App\Filament\Resources\User;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use App\Classes\FilamentUtility;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use App\Models\User;
use App\Filament\Resources\User\ResourcePages\Create;
use App\Filament\Resources\User\ResourcePages\Edit;
use App\Filament\Resources\User\ResourcePages\ListItems;
use App\Filament\Resources\UserResource\Pages;
use App\Traits\AllColumnActionVisibility;
use App\Traits\CommonActionButtons;
use App\Traits\ToggleColumnVisibility;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Database\Eloquent\Collection;
use Filament\Forms\Components\Checkbox;

class UserResource extends Resource
{
    use AllColumnActionVisibility, CommonActionButtons;

    protected static ?string $model = User::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-s-user';

    public static function getNavigationBadge(): ?string
    {
        return self::$model::count();
    }

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __("Users");
    }

    public static function getModelLabel(): string
    {
        return __("Users");
    }

    public static function getPluralLabel(): ?string
    {
        return __("Users");
    }

    public static function getPluralModelLabel(): string
    {
        return __("Users");
    }


    public static function getNavigationGroup(): ?string
    {
        return Utils::isResourceNavigationGroupEnabled()
            ? __('filament-shield::filament-shield.nav.group')
            : '';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                Grid::make(3)->schema([
                    Section::make()
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([

                        TextInput::make('username')
                            ->label(__("User Name"))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->notRegex('/<[^b][^r][^>]*>/')
                            ->validationMessages([
                                'not_regex' => 'HTML is invalid',
                            ]),


                        TextInput::make('email')
                            ->label(__("Email"))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->email()
                            ->maxLength(255)
                            ->notRegex('/<[^b][^r][^>]*>/')
                            ->validationMessages([
                                'not_regex' => 'HTML is invalid',
                            ]),


                        TextInput::make('first_name')
                            ->label(__("First Name"))
                            ->required()
                            ->maxLength(255)
                            ->notRegex('/<[^b][^r][^>]*>/')
                            ->validationMessages([
                                'not_regex' => 'HTML is invalid',
                            ]),


                        TextInput::make('last_name')
                            ->label(__("Last Name"))
                            ->required()
                            ->maxLength(255)
                            ->notRegex('/<[^b][^r][^>]*>/')
                            ->validationMessages([
                                'not_regex' => 'HTML is invalid',
                            ]),


                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->label(__("Password"))
                            ->required()
                            ->maxLength(255)
                            ->notRegex('/<[^b][^r][^>]*>/')
                            ->validationMessages([
                                'not_regex' => 'HTML is invalid',
                            ])
                            ->rules(['regex:/^(?=(.*[a-z]){2,})(?=(.*[A-Z]){2,}).{8,}$/'])
                            ->helperText(__('At least 8 characters, 2 lowercase and 2 uppercase.'))
                            ->visible(fn (string $operation): bool => $operation === 'create'),

                        TextInput::make('password_confirmation')
                            ->password()
                            ->revealable()
                            ->label(__("Confirm Password"))
                            ->required()
                            ->maxLength(255)
                            ->notRegex('/<[^b][^r][^>]*>/')
                            ->validationMessages([
                                'not_regex' => 'HTML is invalid',
                            ])
                            ->same('password')
                            ->dehydrated(false)
                            ->visible(fn (string $operation): bool => $operation === 'create'),



                        ]),




                    Section::make()
                    ->columnSpan(1)
                    ->schema([

                        Select::make('status')
                            ->label(__("Status"))
                            ->required()
                            ->options(User::getStatusList())
                            ->default(User::STATUS_ACTIVE)
                            ->native(false)
                            ->selectablePlaceholder(false)
                            ->notRegex('/<[^b][^r][^>]*>/')
                            ->validationMessages([
                                'not_regex' => 'HTML is invalid',
                            ])
                            ->visible(fn ($record) => !$record || $record->id !== 1),

                    ]),

                ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('username')
                    ->label(__("User Name"))
                    ->searchable(),



                TextColumn::make('email')
                    ->searchable()
                    ->alignCenter(),

                TextColumn::make('registration_ip')
                    ->searchable()
                    ->alignCenter(),


                ToggleColumn::make('status')
                    ->label(__("Status"))
                    ->alignCenter()
                    ->visible( static::toggleColumnVisibility() ),


                FilamentUtility::createdAtColumn(),
                FilamentUtility::updatedAtColumn(),





            ])
            ->striped()
            ->filters([

                SelectFilter::make('status')
                    ->options(User::getStatusList()),

                TrashedFilter::make(),
            ])
            ->recordActions(

                static::renderTableActions([

                    Action::make('Change Password')
                        ->action(function (User $record, array $data): void {
                            $record->password = $data['password'];
                            $record->save();
                        })
                        ->schema([

                            TextInput::make('password')
                                ->password()
                                ->label(__("Password"))
                                ->required()
                                ->maxLength(255)
                                ->notRegex('/<[^b][^r][^>]*>/')
                                ->validationMessages([
                                    'not_regex' => 'HTML is invalid',
                                ])
                                ->minLength(8)
                                ->rules(['regex:/^(?=(.*[a-z]){2,})(?=(.*[A-Z]){2,})(?=(.*\d){2,})(?=(.*[\W_]){2,}).{8,}$/'])
                                ->helperText('Password must be at least 8 characters long and contain 2 lowercase, 2 uppercase, 2 numbers, and 2 special characters.'),

                            TextInput::make('password_confirmation')
                                ->password()
                                ->label(__("Confirm Password"))
                                ->required()
                                ->maxLength(255)
                                ->notRegex('/<[^b][^r][^>]*>/')
                                ->validationMessages([
                                    'not_regex' => 'HTML is invalid',
                                ])
                                ->same('password')
                                ->dehydrated(false),
                        ])
                        ->icon('fas-user-lock')
                        ->iconButton()
                        ->color("success"),
                ]),

            )
            ->toolbarActions([
                BulkActionGroup::make([

                    BulkAction::make('publish')
                    ->action(fn (Collection $records) => static::BulkActivate($records))
                    ->deselectRecordsAfterCompletion()
                    ->requiresConfirmation()
                    ->color('success')
                    ->icon('fas-toggle-on')
                    ->visible( static::toggleColumnVisibility() ),

                    BulkAction::make('pending')
                        ->action(fn (Collection $records) => static::BulkDeactivate($records))
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation()
                        ->color('warning')
                        ->icon('fas-toggle-off')
                        ->visible( static::toggleColumnVisibility() ),
                    DeleteBulkAction::make(),
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
            'index' => ListItems::route('/'),
            'create' => Create::route('/create'),
            'edit' => Edit::route('/{record}/edit'),
        ];
    }
}
