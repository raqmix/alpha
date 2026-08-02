<?php

namespace App\Filament\Resources\Button;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use App\Filament\Resources\Button\ResourcePages\ListItems;
use App\Filament\Resources\Button\ResourcePages\Create;
use App\Filament\Resources\Button\ResourcePages\Edit;
use App\Classes\CustomPackage\CustomComponent\CustomCuratorPicker;
use App\Classes\CustomPackage\CustomComponent\CustomIconPicker;
use App\Classes\FilamentUtility;
use App\Filament\Resources\Bms\Model\Bms;
use App\Filament\Resources\Button\Model\Button;
use App\Filament\Resources\Button\ResourcePages;
use App\Filament\Resources\DropdownList\DropdownListResource;
use App\Filament\Resources\DropdownList\Model\DropdownList;
use App\Traits\AllColumnActionVisibility;
use App\Traits\CommonActionButtons;
use Awcodes\Curator\PathGenerators\DatePathGenerator;
use CactusGalaxy\FilamentAstrotomic\Resources\Concerns\ResourceTranslatable;
use CactusGalaxy\FilamentAstrotomic\TranslatableTab;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Awcodes\Curator\Components\Tables\CuratorColumn;
use App\Filament\Forms\Components\TranslatableTabsNoArabic;
use Filament\Forms\Components\TextInput;

class ButtonResource extends Resource
{

    use ResourceTranslatable, AllColumnActionVisibility, CommonActionButtons;

    protected static ?string $model = Button::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-s-cursor-arrow-rays';

    public static function getNavigationBadge(): ?string
    {
        return self::$model::count();
    }

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __("BUTTONS");
    }

    public static function getModelLabel(): string
    {
        return __("BUTTONS");
    }

    public static function getPluralLabel(): ?string
    {
        return __("BUTTONS");
    }

    public static function getPluralModelLabel(): string
    {
        return __("BUTTONS");
    }

    public static function getNavigationGroup(): ?string
    {
        return __("Default Enginees");
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

                                TextInput::make($tab->makeName('url'))
                                    ->label(__("Url[" . $tab->getLocale() . "]"))
                                    ->maxLength(510)
                                    ->notRegex('/<[^b][^r][^>]*>/')
                                    ->validationMessages([
                                        'not_regex' => 'HTML is invalid',
                                    ]),


                                TextInput::make($tab->makeName('label'))
                                    ->label(__("Label[" . $tab->getLocale() . "]"))
                                    ->maxLength(255)
                                    ->notRegex('/<[^b][^r][^>]*>/')
                                    ->validationMessages([
                                        'not_regex' => 'HTML is invalid',
                                    ]),


                            ]),

                        Section::make()
                            ->columnSpan(1)
                            ->schema([


                                CustomIconPicker::make('icon'),

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
                                    ->options(Bms::getStatusList())
                                    ->default(Bms::STATUS_PUBLISHED)
                                    ->native(false)
                                    ->selectablePlaceholder(false)
                                    ->notRegex('/<[^b][^r][^>]*>/')
                                    ->validationMessages([
                                        'not_regex' => 'HTML is invalid',
                                    ]),


                                Select::make('category_slug')
                                    ->label(__("Category"))
                                    ->required()
                                    ->relationship('category', 'title', fn(Builder $query) => $query->where('category', DropdownList::BUTTON_CATEGORY))
                                    ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->title}")
                                    ->native(false)
                                    ->createOptionModalHeading('Create')
                                    ->createOptionForm(fn(Schema $schema) => DropdownListResource::formPopup($schema, DropdownList::BUTTON_CATEGORY))
                                    ->createOptionUsing(function (array $data) {

                                        $optionRecord = DropdownList::create($data);

                                        return $optionRecord->slug;

                                    })
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


    public static function formPopup(Schema $schema, $categorySlug): Schema
    {

        return $schema->components([

            TranslatableTabsNoArabic::make()
                ->columnSpan(2)
                ->localeTabSchema(fn(TranslatableTab $tab) => [

                    Grid::make(3)->schema([
                        Section::make()
                            ->columnSpan(2)
                            ->schema([

                                TextInput::make($tab->makeName('url'))
                                    ->label(__("Url[" . $tab->getLocale() . "]"))
                                    ->required()
                                    ->maxLength(510)
                                    ->notRegex('/<[^b][^r][^>]*>/')
                                    ->validationMessages([
                                        'not_regex' => 'HTML is invalid',
                                    ]),


                                TextInput::make($tab->makeName('label'))
                                    ->label(__("Label[" . $tab->getLocale() . "]"))
                                    ->maxLength(255)
                                    ->notRegex('/<[^b][^r][^>]*>/')
                                    ->validationMessages([
                                        'not_regex' => 'HTML is invalid',
                                    ]),


                            ]),

                        Section::make()
                            ->columnSpan(1)
                            ->schema([


                                CustomIconPicker::make('icon'),

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
                                    ->options(Bms::getStatusList())
                                    ->default(Bms::STATUS_PUBLISHED)
                                    ->native(false)
                                    ->selectablePlaceholder(false)
                                    ->notRegex('/<[^b][^r][^>]*>/')
                                    ->validationMessages([
                                        'not_regex' => 'HTML is invalid',
                                    ]),


                                TextInput::make('category_slug')
                                    ->label(__("Category"))
                                    ->readOnly()
                                    ->required()
                                    ->default($categorySlug),


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



                CuratorColumn::make("image_id")
                    ->label('image'),



                TextColumn::make('url')
                    ->label(__("Url"))
                    ->searchable(
                        isIndividual: false,
                        query: function (Builder $query, string $search): Builder {
                            return $query
                                ->whereHas("translations", function ($query) use ($search) {
                                    $query->where('url', 'like', "%{$search}%")
                                        ->where("language", "en");
                                });
                        }
                    )->words('5')->html(),


                TextColumn::make('label')
                    ->label(__("Label"))
                    ->searchable(
                        isIndividual: false,
                        query: function (Builder $query, string $search): Builder {
                            return $query
                                ->whereHas("translations", function ($query) use ($search) {
                                    $query->where('label', 'like', "%{$search}%")
                                        ->where("language", "en");
                                });
                        }
                    )->words('5')->html(),



                FilamentUtility::publishedAtColumn(),


                SelectColumn::make('category_slug')
                    ->label(__("Category"))
                    ->options(Button::getButtonCategoryList())
                    ->alignCenter()
                    ->selectablePlaceholder(false)
                    ->visible(static::selectColumnVisibility()),


                FilamentUtility::statusColumn(static::$model),
                FilamentUtility::createdAtColumn(),
                FilamentUtility::updatedAtColumn(),

                FilamentUtility::creatorColumn(),
                FilamentUtility::updaterColumn(),



            ])
            ->defaultSort(function (Builder $query): Builder {
                return $query
                    ->whereIn("category_slug", array_keys(Button::getButtonCategoryList()->toArray()))
                    // ->withoutUnReviewed()
                    ->orderBy('weight_order', 'asc')
                    ->orderBy('published_at', 'desc');
            })
            ->striped()
            ->reorderable('weight_order')

            ->filters(

                static::renderFilterActions([
                    SelectFilter::make('category_slug')
                        ->options(Button::getButtonCategoryList()),
                ]),
            )

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
