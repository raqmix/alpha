<?php

namespace App\Filament\Resources\District;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SelectColumn;
use App\Classes\FilamentUtility;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\BulkActionGroup;
use App\Filament\Resources\City\CityResource;
use App\Filament\Forms\Components\TranslatableTabsNoArabic;
use CactusGalaxy\FilamentAstrotomic\Resources\Concerns\ResourceTranslatable;
use CactusGalaxy\FilamentAstrotomic\TranslatableTab;


use App\Traits\AllColumnActionVisibility;
use App\Filament\Resources\District\Exports\Exporter;
use App\Filament\Resources\District\Imports\Importer;
use App\Filament\Resources\District\Model\District;
use App\Filament\Resources\District\ResourcePages\Create;
use App\Filament\Resources\District\ResourcePages\Edit;
use App\Filament\Resources\District\ResourcePages\ListItems;
use App\Traits\CommonActionButtons;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DistrictResource extends Resource
{
    use ResourceTranslatable, AllColumnActionVisibility, CommonActionButtons;

    protected static ?string $model = District::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-office';


    public static function getNavigationBadge(): ?string
    {
        return self::$model::count();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
    public static function canAccess(): bool
    {
        return false;
    }


    protected static ?int $navigationSort = 3;


    public static function getNavigationLabel(): string
    {
        return __("District");
    }

    public static function getModelLabel(): string
    {
        return __("District");
    }

    public static function getPluralLabel(): ?string
    {
        return __("District");
    }

    public static function getPluralModelLabel(): string
    {
        return __("District");
    }

    public static function getNavigationGroup(): ?string
    {
        return __("Locations");
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
                                    ->required()
                                    ->maxLength(255)
                                    ->notRegex('/<[^b][^r][^>]*>/')
                                    ->validationMessages([
                                        'not_regex' => 'HTML is invalid',
                                    ]),

                                TextInput::make('slug')
                                    ->label(__("Slug"))
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Generated Automatically')
                                    ->maxLength(255)
                                    ->readOnly()
                                    ->notRegex('/<[^b][^r][^>]*>/')
                                    ->validationMessages([
                                        'not_regex' => 'HTML is invalid',
                                    ]),



                                Select::make('city_id')
                                    ->label(__("City"))
                                    ->required()
                                    ->relationship('city', 'title')
                                    ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->title}")
                                    ->native(false)
                                    ->createOptionModalHeading('Create')
                                    ->createOptionForm(fn(Schema $schema) => CityResource::form($schema))
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
                                    ->options(self::$model::getStatusList())
                                    ->default(self::$model::STATUS_PUBLISHED)
                                    ->native(false)
                                    ->selectablePlaceholder(false)
                                    ->notRegex('/<[^b][^r][^>]*>/')
                                    ->validationMessages([
                                        'not_regex' => 'HTML is invalid',
                                    ]),


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
                    ),

                TextColumn::make('title:ar')
                    ->label(__("title Ar"))
                    ->searchable(
                        isIndividual: false,
                        query: function (Builder $query, string $search): Builder {
                            return $query
                                ->whereHas("translations", function ($query) use ($search) {
                                    $query->where('title', 'like', "%{$search}%")
                                        ->where("language", "ar");
                                });
                        }
                    ),

                SelectColumn::make('city_id')
                    ->label(__("City"))
                    ->options(District::getCityList())
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
                    SelectFilter::make('city_id')
                        ->label(__("City"))
                        ->options(District::getCityList()),
                ]),
            )

            ->recordActions(
                static::renderTableActions(),
            )


            ->headerActions(

                static::renderHeaderActions(Importer::class, Exporter::class),

            )
            ->toolbarActions([
                BulkActionGroup::make(
                    static::renderBulkActions(Exporter::class)

                ),
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
