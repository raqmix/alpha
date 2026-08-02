<?php

namespace App\Filament\Resources\Menu;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use App\Classes\FilamentUtility;
use Filament\Actions\Action;
use App\Filament\Resources\DropdownList\DropdownListResource;
use App\Filament\Resources\DropdownList\Model\DropdownList;
use App\Filament\Resources\Menu\Model\Menu;
use App\Filament\Resources\MenuLink\ResourcePages\Create;
use App\Filament\Resources\MenuLink\ResourcePages\Edit;
use App\Filament\Resources\MenuLink\ResourcePages\ListItems;
use App\Filament\Resources\MenuLink\ResourcePages\MenuLinkTree;


use App\Traits\AllColumnActionVisibility;
use App\Traits\CommonActionButtons;
use App\Filament\Forms\Components\TranslatableTabsNoArabic;
use CactusGalaxy\FilamentAstrotomic\Resources\Concerns\ResourceTranslatable;
use CactusGalaxy\FilamentAstrotomic\TranslatableTab;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;


class MenuResource extends Resource
{
    use ResourceTranslatable, CommonActionButtons, AllColumnActionVisibility;

    protected static ?string $model = Menu::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-queue-list';

    public static function getNavigationBadge(): ?string
    {
        return self::$model::count();
    }

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __("Menu");
    }

    public static function getModelLabel(): string
    {
        return __("Menu");
    }

    public static function getPluralLabel(): ?string
    {
        return __("Menu");
    }

    public static function getPluralModelLabel(): string
    {
        return __("Menu");
    }

    public static function getNavigationGroup(): ?string
    {
        return __("Setting");
    }




    public static function getRecordTitle(?Model $record): string|null|Htmlable
    {
        return $record->title;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }




    public static function form(Schema $schema): Schema
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
                                    ->required($tab->getLocale() === 'en')
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


                                // \Filament\Forms\Components\Select::make('category_slug')
                                //     ->label(__("Category"))
                                //     ->required()
                                //     ->relationship('category', 'title', fn(Builder $query) => $query->where('category', DropdownList::MENU_CATEGORY))
                                //     ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->title}")
                                //     ->native(false)
                                //     ->createOptionModalHeading('Create')
                                //     ->createOptionForm(fn(Form $form) => DropdownListResource::formPopup($form, DropdownList::MENU_CATEGORY))
                                //     ->createOptionUsing(function (array $data) {

                                //         $optionRecord = DropdownList::create($data);

                                //         return $optionRecord->slug;

                                //     })
                                //     ->notRegex('/<[^b][^r][^>]*>/')
                                //     ->validationMessages([
                                //         'not_regex' => 'HTML is invalid',
                                //     ]),


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



                // \App\Classes\FilamentUtility::publishedAtColumn(),


                // Tables\Columns\SelectColumn::make('category_slug')
                //     ->label(__("Category"))
                //     ->options(Menu::getCategoryList())
                //     ->alignCenter()
                //     ->selectablePlaceholder(false),

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

            // ->filters(
            //     static::renderFilterActions([
            //         Tables\Filters\SelectFilter::make('category_slug')
            //             ->label("Category")
            //             ->options(Menu::getCategoryList()),
            //     ]),
            // )

            ->recordActions([
                Action::make('Menu Link')
                    ->url(
                        fn(Menu $record): string => static::getUrl('menu-links.menu-link-list', [
                            'parent' => $record->id,
                        ])
                    ),
            ])
            ->toolbarActions([]);
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
            'index' => ResourcePages\ListItems::route('/'),
            'create' => ResourcePages\Create::route('/create'),
            'edit' => ResourcePages\Edit::route('/{record}/edit'),

            // menuLinks
            'menu-links.index' => ListItems::route('/{parent}/menu-links'),
            'menu-links.create' => Create::route('/{parent}/menu-links/create'),
            'menu-links.edit' => Edit::route('/{parent}/menu-links/{record}/edit'),
            'menu-links.menu-link-list' => MenuLinkTree::route('/{parent}/menu-link-list'),
        ];
    }

}
