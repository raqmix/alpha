<?php

namespace App\Filament\Resources\Seo;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use App\Classes\FilamentUtility;
use Filament\Actions\BulkActionGroup;
use App\Classes\CustomPackage\CustomComponent\CustomCuratorPicker;
use App\Filament\Resources\Seo\Exports\Exporter;
use App\Filament\Resources\Seo\Imports\Importer;
use App\Filament\Resources\Seo\Model\Seo;
use App\Filament\Resources\Seo\ResourcePages\Create;
use App\Filament\Resources\Seo\ResourcePages\Edit;
use App\Filament\Resources\Seo\ResourcePages\ListItems;
use App\Filament\Resources\SeoResource\Pages;
use Awcodes\Curator\Components\Tables\CuratorColumn;
use Awcodes\Curator\PathGenerators\DatePathGenerator;
use App\Filament\Forms\Components\TranslatableTabsNoArabic;
use CactusGalaxy\FilamentAstrotomic\Resources\Concerns\ResourceTranslatable;
use CactusGalaxy\FilamentAstrotomic\TranslatableTab;
use Carbon\Carbon;
use Filament\Forms;

use App\Traits\AllColumnActionVisibility;


use App\Traits\CommonActionButtons;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SeoResource extends Resource
{
    use ResourceTranslatable, AllColumnActionVisibility,CommonActionButtons;

    protected static ?string $model = Seo::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-signal';

    public static function getNavigationBadge(): ?string
    {
        return self::$model::count();
    }

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __("Seo");
    }

    public static function getModelLabel(): string
    {
        return __("Seo");
    }

    public static function getPluralLabel(): ?string
    {
        return __("Seo");
    }

    public static function getPluralModelLabel(): string
    {
        return __("Seo");
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

        return $schema->components([

            TranslatableTabsNoArabic::make()
                ->columnSpanFull()
                ->localeTabSchema(fn (TranslatableTab $tab) => [

                    Grid::make(3)->schema([
                        Section::make()
                            ->columnSpan(2)
                            ->schema([


                                TextInput::make('path')
                                ->label(__("path"))
                                ->unique(ignoreRecord: true)
                                ->required()
                                ->maxLength(255)
                                ->notRegex('/<[^b][^r][^>]*>/')
                                ->validationMessages([
                                    'not_regex' => 'HTML is invalid',
                                ]),


                                Select::make('robots')
                                ->label(__('filament-seo::translations.robots'))
                                ->options([
                                    'index, follow' => 'Index, Follow',
                                    'index, nofollow' => 'Index, Nofollow',
                                    'noindex, follow' => 'Noindex, Follow',
                                    'noindex, nofollow' => 'Noindex, Nofollow',
                                ]),


                                TextInput::make($tab->makeName('title'))
                                ->label(__("Title[". $tab->getLocale()."]"))
                                ->required()
                                ->maxLength(255)
                                ->notRegex('/<[^b][^r][^>]*>/')
                                ->validationMessages([
                                    'not_regex' => 'HTML is invalid',
                                ]),

                                Textarea::make($tab->makeName('description'))
                                ->label(__("Description[". $tab->getLocale()."]"))
                                ->maxLength(255)
                                ->notRegex('/<[^b][^r][^>]*>/')
                                ->validationMessages([
                                    'not_regex' => 'HTML is invalid',
                                ]),

                                TextInput::make($tab->makeName('author'))
                                ->label(__("Author[". $tab->getLocale()."]"))
                                ->maxLength(255)
                                ->notRegex('/<[^b][^r][^>]*>/')
                                ->validationMessages([
                                    'not_regex' => 'HTML is invalid',
                                ]),

                                Textarea::make($tab->makeName('keywords'))
                                ->label(__("Keywords[". $tab->getLocale()."]"))
                                ->maxLength(255)
                                ->notRegex('/<[^b][^r][^>]*>/')
                                ->validationMessages([
                                    'not_regex' => 'HTML is invalid',
                                ]),


                                CustomCuratorPicker::make($tab->makeName("image_id"))
                                    ->label(__("Main Image[". $tab->getLocale()."]"))
                                    ->pathGenerator(DatePathGenerator::class)
                                    ->size(40)
                                    ->color('primary')
                                    ->outlined(true)
                                    ->size('md')
                                    ->constrained(false)
                                    ->orderColumn('order'),



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
                                        ->options(Seo::getStatusList())
                                        ->default(Seo::STATUS_PUBLISHED)
                                        ->native(false)
                                        ->selectablePlaceholder(false)
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


                TextColumn::make('path')
                    ->label(__("path"))
                    ->searchable()
                    ->words('5')
                    ->html(),


                // \App\Classes\FilamentUtility::publishedAtColumn(),

                FilamentUtility::statusColumn(static::$model),
                FilamentUtility::createdAtColumn(),
                FilamentUtility::updatedAtColumn(),

                FilamentUtility::creatorColumn(),
                FilamentUtility::updaterColumn(),




            ])
            ->defaultSort(function (Builder $query): Builder {
                return $query
                ->orderBy('published_at', 'desc');
            })
            ->striped()

            ->filters(
                static::renderFilterActions(),
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
