<?php

namespace App\Filament\Resources\MenuLink;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SelectColumn;
use App\Classes\FilamentUtility;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use App\Filament\Resources\MenuLink\ResourcePages\ListItems;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\Menu\MenuResource;
use App\Filament\Resources\Menu\Model\Menu;
use App\Filament\Resources\MenuLink\Model\MenuLink;
use App\Filament\Forms\Components\TranslatableTabsNoArabic;
use CactusGalaxy\FilamentAstrotomic\Resources\Concerns\ResourceTranslatable;
use CactusGalaxy\FilamentAstrotomic\TranslatableTab;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use App\Traits\CommonActionButtons;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;


class MenuLinkResource extends Resource
{
    use ResourceTranslatable, CommonActionButtons;

    protected static ?string $model = MenuLink::class;

    /**
     * Renamed from $parentResource: Filament v5 declares its own
     * protected static ?string $parentResource for native nested resources,
     * which this project's custom App\Traits\HasParentResource predates.
     */
    public static string $customParentResource = MenuResource::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-newspaper';

    public static function getNavigationBadge(): ?string
    {
        return self::$model::count();
    }

    protected static bool $shouldRegisterNavigation = false;


    public static function getNavigationLabel(): string
    {
        return __("Menu Link");
    }

    public static function getModelLabel(): string
    {
        return __("Menu Link");
    }

    public static function getPluralLabel(): ?string
    {
        return __("Menu Link");
    }

    public static function getPluralModelLabel(): string
    {
        return __("Menu Link");
    }

    public static function getNavigationGroup(): ?string
    {
        return __("Setting");
    }

    public static function getRecordTitle(?Model $record): string|null|Htmlable
    {
        return $record->title;
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
                                    ->maxLength(255)
                                    ->notRegex('/<[^b][^r][^>]*>/')
                                    ->validationMessages([
                                        'not_regex' => 'HTML is invalid',
                                    ]),

                                TextInput::make($tab->makeName('link'))
                                    ->label(__("Link[" . $tab->getLocale() . "]"))
                                    ->maxLength(255)
                                    ->notRegex('/<[^b][^r][^>]*>/')
                                    ->validationMessages([
                                        'not_regex' => 'HTML is invalid',
                                    ]),

                                //custom_color_class
                                TextInput::make('custom_color_class')
                                    ->label(__("Custom Color Class"))
                                    ->default('')
                                    ->visible($tab->getLocale() === 'en')
                                    ->dehydrated($tab->getLocale() === 'en')
                                    ->dehydrateStateUsing(fn ($state) => $state ?? '')
                                    ->maxLength(255)
                                    ->notRegex('/<[^b][^r][^>]*>/')
                                    ->validationMessages([
                                        'not_regex' => 'HTML is invalid',


                                    ]),
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
                                    ->options(Menu::getStatusList())
                                    ->default(Menu::STATUS_PUBLISHED)
                                    ->native(false)
                                    ->selectablePlaceholder(false)
                                    ->notRegex('/<[^b][^r][^>]*>/')
                                    ->validationMessages([
                                        'not_regex' => 'HTML is invalid',
                                    ]),


                                Select::make('menu_id')
                                    ->label(__("Main Menu"))
                                    ->required()
                                    ->native(false)
                                    ->searchable()
                                    ->options(
                                        Menu::active()
                                            ->translated()
                                            ->get()
                                            ->mapWithKeys(function ($i) {
                                                return [$i->id => $i->title];
                                            })
                                    )
                                    ->notRegex('/<[^b][^r][^>]*>/')
                                    ->validationMessages([
                                        'not_regex' => 'HTML is invalid',
                                    ])
                                    ->default(fn($livewire) => $livewire->parent->id),


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
                    )->words('5')->html(),



                SelectColumn::make('menu_id')
                    ->label(__("Menu"))
                    ->options(Menu::getAllList())
                    ->alignCenter()
                    ->selectablePlaceholder(false),




                FilamentUtility::publishedAtColumn(),



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

            ->filters(
                static::renderFilterActions([
                    SelectFilter::make('menu_id')
                        ->label("Menu")
                        ->options(Menu::getAllList()),
                ]),
            )

            ->recordActions([
                EditAction::make()
                    ->url(
                        fn(ListItems $livewire, Model $record): string => static::$customParentResource::getUrl('menu-links.edit', [
                            'record' => $record,
                            'parent' => $livewire->parent,
                        ])
                    ),
                DeleteAction::make()->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
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


}
