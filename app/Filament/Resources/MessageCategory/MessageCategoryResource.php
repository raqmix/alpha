<?php

namespace App\Filament\Resources\MessageCategory;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use App\Classes\FilamentUtility;
use Filament\Actions\BulkActionGroup;
use App\Filament\Resources\MessageCategory\Exports\MessageCategoryExporter;
use App\Filament\Resources\MessageCategory\Imports\MessageCategoryImporter;
use App\Filament\Resources\MessageCategory\Model\MessageCategory;
use App\Filament\Resources\MessageCategory\RelationManagers\MessagesRelationManager;
use App\Filament\Resources\MessageCategory\ResourcePages\Edit;
use App\Filament\Resources\MessageCategory\ResourcePages\ListItems;
use App\Traits\AllColumnActionVisibility;
use App\Traits\CommonActionButtons;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions\DeleteAction;
use Filament\Tables\Table;

class MessageCategoryResource extends Resource
{
    use AllColumnActionVisibility, CommonActionButtons;

    protected static ?string $slug = 'message-translation';

    protected static ?string $model = MessageCategory::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-globe-alt';


    public static function getNavigationBadge(): ?string
    {
        return self::$model::count();
    }

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __("admin.Messag Translation");
    }

    public static function getModelLabel(): string
    {
        return __("admin.Messag Category");
    }

    public static function getPluralLabel(): ?string
    {
        return __("admin.Messag Categories");
    }

    public static function getPluralModelLabel(): string
    {
        return __("admin.Messag Categories");
    }

    public static function getNavigationGroup(): ?string
    {
        return __("Setting");
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
                ->columns(1)
                ->components([

                    Grid::make(3)->schema([

                        Section::make()
                        ->columnSpan(2)
                        ->schema([

                                TextInput::make('title')
                                    ->label(__("Title"))
                                    ->autofocus()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->required()
                                    ->notRegex('/<[^b][^r][^>]*>/')
                                    ->validationMessages([
                                        'not_regex' => 'HTML is invalid',
                                    ]),
                        ]),



                        Section::make()
                        ->columnSpan(1)
                        ->schema([

                            Select::make('status')
                                ->label(__("Status"))
                                ->required()
                                ->options(MessageCategory::getStatusList())
                                ->default(MessageCategory::STATUS_PUBLISHED)
                                ->native(false)
                                ->selectablePlaceholder(false)
                                ->notRegex('/<[^b][^r][^>]*>/')
                                ->validationMessages([
                                    'not_regex' => 'HTML is invalid',
                                ]),


                        ]),


                    ]),

                ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([

            TextColumn::make('title')
            ->label(__("title"))
            ->searchable(),


            FilamentUtility::statusColumn(static::$model),
            FilamentUtility::createdAtColumn(),
            FilamentUtility::updatedAtColumn(),

            FilamentUtility::creatorColumn(),
            FilamentUtility::updaterColumn(),

        ])
        ->defaultSort('id', 'desc')
        ->striped()

        ->filters(
            static::renderFilterActions(),
        )
        ->recordActions(
            static::renderTableActions(),
        )
        ->headerActions(

            static::renderHeaderActions(MessageCategoryImporter::class, MessageCategoryExporter::class),

        )
        ->toolbarActions(
            [
                BulkActionGroup::make(
                    static::renderBulkActions(MessageCategoryExporter::class)

                ),
            ]
        );
    }

    public static function getRelations(): array
    {
        return [
            MessagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListItems::route('/'),
            // 'create' => Pages\CreateMessageCategory::route('/create'),
            'edit' => Edit::route('/{record}/edit'),
        ];
    }
}
