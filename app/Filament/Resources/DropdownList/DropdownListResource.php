<?php

namespace App\Filament\Resources\DropdownList;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use App\Classes\FilamentUtility;
use App\Filament\Forms\Components\TranslatableTabsNoArabic;
use CactusGalaxy\FilamentAstrotomic\Resources\Concerns\ResourceTranslatable;
use CactusGalaxy\FilamentAstrotomic\TranslatableTab;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Resources\Resource;
use App\Traits\CommonActionButtons;

use App\Traits\AllColumnActionVisibility;
use App\Filament\Resources\DropdownList\Model\DropdownList;
use App\Filament\Resources\DropdownList\ResourcePages\Create;
use App\Filament\Resources\DropdownList\ResourcePages\Edit;
use App\Filament\Resources\DropdownList\ResourcePages\ListItems;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class DropdownListResource extends Resource
{
    use ResourceTranslatable, AllColumnActionVisibility, CommonActionButtons;

    protected static ?string $model = DropdownList::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-c-list-bullet';


    public static function getNavigationBadge(): ?string
    {
        return self::$model::count();
    }



    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __("Categories");
    }

    public static function getModelLabel(): string
    {
        return __(" Categories");
    }

    public static function getPluralLabel(): ?string
    {
        return __("Categories");
    }

    public static function getPluralModelLabel(): string
    {
        return __("Categories");
    }

    public static function getNavigationGroup(): ?string
    {
        return __("Default Enginees");
    }




    public static function form(Schema $schema): Schema
    {

        return $schema->columns(1)->components([

            TranslatableTabsNoArabic::make()
                ->columnSpan(2)
                ->localeTabSchema(fn(TranslatableTab $tab) => [

                    Grid::make(3)->schema([
                        Section::make()
                            ->columnSpan(2)
                            ->schema([

                                TextInput::make($tab->makeName('title'))
                                    ->label(__("Title[" . $tab->getLocale() . "]"))
                                    ->required($tab->getLocale() === 'en')
                                    ->maxLength(1000)
                                    ->notRegex('/<[^b][^r][^>]*>/')
                                    ->validationMessages([
                                        'not_regex' => 'HTML is invalid',
                                    ]),
                                TextInput::make('slug')
                                    ->label(__("Slug"))
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Generated Automatically')
                                    ->maxLength(255)
                                    ->notRegex('/<[^b][^r][^>]*>/')
                                    ->validationMessages([
                                        'not_regex' => 'HTML is invalid',
                                    ]),

                                // Forms\Components\Textarea::make($tab->makeName('brief'))
                                //     ->label(__("Brief[". $tab->getLocale()."]"))
                                //     ->maxLength(1000)
                                //     ->rows(6)
                                //     ->notRegex('/<[^b][^r][^>]*>/')
                                //     ->validationMessages([
                                //         'not_regex' => 'HTML is invalid',
                                //     ]),


                                // TinyEditor::make($tab->makeName('content'))
                                //     ->label(__("Content[". $tab->getLocale()."]"))
                                //     ->showMenuBar()
                                //     ->toolbarSticky(true)
                                //     ->profile('with-bootstrap'),



                            ]),

                        Section::make()
                            ->columnSpan(1)
                            ->schema([

                                DatePicker::make('published_at')
                                    ->label(__("Published At"))
                                    ->rules(['date_format:Y-m-d', 'regex:/^\d{4}-\d{2}-\d{2}$/'])
                                    ->default(Carbon::now())
                                    ->required()
                                    ->notRegex('/<[^b][^r][^>]*>/')
                                    ->validationMessages([
                                        'not_regex' => 'HTML is invalid',
                                    ]),

                                Select::make('status')
                                    ->label(__("Status"))
                                    ->required()
                                    ->options(DropdownList::getStatusList())
                                    ->default(DropdownList::STATUS_PUBLISHED)
                                    ->native(false)
                                    ->selectablePlaceholder(false)
                                    ->notRegex('/<[^b][^r][^>]*>/')
                                    ->validationMessages([
                                        'not_regex' => 'HTML is invalid',
                                    ]),

                                Select::make('category')
                                    ->label(__("Category"))
                                    ->required()
                                    ->options(DropdownList::getCategoryList())
                                    ->native(false)
                                    ->searchable()
                                    ->notRegex('/<[^b][^r][^>]*>/')
                                    ->validationMessages([
                                        'not_regex' => 'HTML is invalid',
                                    ])
                                    ->reactive(),


                                TextInput::make('weight_order')
                                    ->label(__("Weight Order"))
                                    ->required()
                                    ->integer()
                                    ->default(10)
                                    ->notRegex('/<[^b][^r][^>]*>/')
                                    ->validationMessages([
                                        'not_regex' => 'HTML is invalid',
                                    ]),



                            ]),
                    ])
                ])

        ]);
    }

    public static function formPopup(Schema $schema, $defaultCategory): Schema
    {

        return $schema->components([

            TranslatableTabsNoArabic::make()
                ->columnSpan(2)
                ->localeTabSchema(fn(TranslatableTab $tab) => [

                    Grid::make(3)->schema([
                        Section::make()
                            ->columnSpan(2)
                            ->schema([

                                TextInput::make($tab->makeName('title'))
                                    ->label(__("Title[" . $tab->getLocale() . "]"))
                                    ->required()
                                    ->maxLength(255)
                                    ->notRegex('/<[^b][^r][^>]*>/')
                                    ->validationMessages([
                                        'not_regex' => 'HTML is invalid',
                                    ]),

                                TextInput::make('slug')
                                    ->label(__("Slug"))
                                    ->unique()
                                    ->required()
                                    ->helperText('Generated Automatically')
                                    ->maxLength(255),



                            ]),

                        Section::make()
                            ->columnSpan(1)
                            ->schema([

                                DatePicker::make('published_at')
                                    ->label(__("Published At"))
                                    ->default(Carbon::now())
                                    ->required(),

                                Select::make('status')
                                    ->label(__("Status"))
                                    ->required()
                                    ->options(DropdownList::getStatusList())
                                    ->default(DropdownList::STATUS_PUBLISHED)
                                    ->native(false)
                                    ->selectablePlaceholder(false)
                                    ->live(),

                                Select::make('category')
                                    ->label(__("Category"))
                                    ->required()
                                    ->options(DropdownList::getCategoryList())
                                    ->native(false)
                                    ->searchable()
                                    ->default($defaultCategory),



                            ]),
                    ])
                ])

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('title')
                    ->label(__("title"))
                    ->searchable(
                        isIndividual: false,
                        query: function (Builder $query, string $search): Builder {
                            return $query
                                ->whereHas("translations", function ($query) use ($search) {
                                    $query->where('title', 'like', "%{$search}%")
                                        ->where("language", "en");
                                });
                        }
                    ),


                FilamentUtility::publishedAtColumn(),


                SelectColumn::make('category')
                    ->label(__("Category"))
                    ->options(DropdownList::getCategoryList())
                    ->alignCenter()
                    ->selectablePlaceholder(false),

                FilamentUtility::statusColumn(static::$model),
                FilamentUtility::createdAtColumn(),
                FilamentUtility::updatedAtColumn(),

                FilamentUtility::creatorColumn(),
                FilamentUtility::updaterColumn(),

            ])
            ->defaultSort(function (Builder $query): Builder {
                return $query
                    ->orderBy('weight_order', 'asc')
                    ->orderBy('published_at', 'desc');
            })
            ->striped()
            ->reorderable('weight_order')

            ->filters(static::renderFilterActions([]))

            ->recordActions(
                static::renderTableActions(),
            )

            ->headerActions([])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('publish')
                        ->action(fn (Collection $records) => static::BulkActivate($records))
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation()
                        ->color('success')
                        ->icon('fas-toggle-on')
                        ->visible(static::toggleColumnVisibility()),
                    BulkAction::make('pending')
                        ->action(fn (Collection $records) => static::BulkDeactivate($records))
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation()
                        ->color('warning')
                        ->icon('fas-toggle-off')
                        ->visible(static::toggleColumnVisibility()),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
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
