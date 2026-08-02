<?php

namespace App\Filament\Resources\Bms;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Fieldset;
use Filament\Actions\Action;
use Livewire\Component;
use Filament\Schemas\Components\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Closure;
use Filament\Tables\Columns\TextColumn;
use App\Classes\FilamentUtility;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use App\Classes\CustomPackage\CustomComponent\CustomCuratorPicker;
use App\Filament\Resources\Bms\Model\Bms;
use App\Filament\Resources\Bms\ResourcePages\Create;
use App\Filament\Resources\Bms\ResourcePages\Edit;
use App\Filament\Resources\Bms\ResourcePages\ListItems;
use App\Filament\Resources\BmsResource\Pages;
use App\Filament\Resources\Button\ButtonResource;
use App\Filament\Resources\Button\Model\Button;
use App\Filament\Resources\DropdownList\DropdownListResource;
use App\Filament\Resources\DropdownList\Model\DropdownList;
use App\Traits\AllColumnActionVisibility;
use App\Traits\CommonActionButtons;
use Awcodes\Curator\Components\Tables\CuratorColumn;
use Awcodes\Curator\PathGenerators\DatePathGenerator;
use App\Filament\Forms\Components\TranslatableTabsNoArabic;
use CactusGalaxy\FilamentAstrotomic\Resources\Concerns\ResourceTranslatable;
use CactusGalaxy\FilamentAstrotomic\TranslatableTab;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Mohamedsabil83\FilamentFormsTinyeditor\Components\TinyEditor;

class BmsResource extends Resource
{
    use ResourceTranslatable, AllColumnActionVisibility, CommonActionButtons;

    protected static ?string $model = Bms::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function getNavigationBadge(): ?string
    {
        return self::$model::count();
    }

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __("Banners");
    }

    public static function getModelLabel(): string
    {
        return __("Banners");
    }

    public static function getPluralLabel(): ?string
    {
        return __("Banners");
    }

    public static function getPluralModelLabel(): string
    {
        return __("Banners");
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

                                TextInput::make($tab->makeName('title'))
                                    ->label(__("Title[" . $tab->getLocale() . "]"))
                                    ->required($tab->getLocale() === 'en')
                                    ->maxLength(510)
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


                                TextInput::make($tab->makeName('second_title'))
                                    ->label(__("Second Title[" . $tab->getLocale() . "]"))

                                    ->maxLength(255)

                                    ->notRegex('/<[^b][^r][^>]*>/')
                                    ->validationMessages([
                                        'not_regex' => 'HTML is invalid',
                                    ]),


                                Textarea::make($tab->makeName('brief'))
                                    ->label(__("Brief[" . $tab->getLocale() . "]"))
                                    ->maxLength(1000)
                                    ->rows(6)
                                    ->notRegex('/<[^b][^r][^>]*>/')
                                    ->validationMessages([
                                        'not_regex' => 'HTML is invalid',
                                    ]),


                                TinyEditor::make($tab->makeName('content'))
                                    ->label(__("Content[" . $tab->getLocale() . "]"))
                                    ->showMenuBar()
                                    ->toolbarSticky(true)
                                    ->profile('with-bootstrap'),

                                TinyEditor::make($tab->makeName('content2'))
                                    ->label(__("Content2[" . $tab->getLocale() . "]"))
                                    ->showMenuBar()
                                    ->toolbarSticky(true)
                                    ->profile('with-bootstrap'),

                                Repeater::make($tab->makeName('json_items'))
                                    ->label(__("Items[" . $tab->getLocale() . "]"))
                                    ->collapsed()
                                    ->addActionLabel(__('Add Item'))
                                    ->itemLabel(function (array $state): ?string {
                                        return $state['label_en']
                                            ?? $state['title_en']
                                            ?? $state['number']
                                            ?? $state['icon']
                                            ?? __('Item');
                                    })
                                    ->schema([
                                        TextInput::make('number')
                                            ->label(__('Number'))
                                            ->nullable(),
                                        TextInput::make('suffix')
                                            ->label(__('Suffix'))
                                            ->nullable(),
                                        TextInput::make('label_en')
                                            ->label(__('Label'))
                                            ->nullable(),
                                        TextInput::make('icon')
                                            ->label(__('Icon'))
                                            ->nullable(),
                                        TextInput::make('title_en')
                                            ->label(__('Title'))
                                            ->nullable(),
                                        Textarea::make('description_en')
                                            ->label(__('Description'))
                                            ->nullable(),
                                    ])
                                    ->columnSpanFull(),

                                CustomCuratorPicker::make($tab->makeName("image_id"))
                                    ->label(__("Main Image[" . $tab->getLocale() . "]"))
                                    ->pathGenerator(DatePathGenerator::class)
                                    ->size(40)
                                    ->color('primary')
                                    ->outlined(true)
                                    ->size('md')
                                    ->constrained(true)
                                    ->listDisplay(false)
                                    ->orderColumn('order')
                                    ->multiple(false),

                                // CustomCuratorPicker::make($tab->makeName("cover_image_id"))
                                //     ->label(__("Cover Image[" . $tab->getLocale() . "]"))
                                //     ->pathGenerator(DatePathGenerator::class)
                                //     ->size(40)
                                //     ->color('primary')
                                //     ->outlined(true)
                                //     ->size('md')
                                //     ->constrained(true)
                                //     ->listDisplay(false)
                                //     ->orderColumn('order')
                                //     ->multiple(false),


                                // CustomCuratorPicker::make($tab->makeName("image_responsive_id"))
                                //     ->label(__("Mobile Image[" . $tab->getLocale() . "]"))
                                //     ->pathGenerator(DatePathGenerator::class)
                                //     ->size(40)
                                //     ->color('primary')
                                //     ->outlined(true)
                                //     ->size('md')
                                //     ->constrained(true)
                                //     ->orderColumn('order'),
                                Fieldset::make("Buttons")
                                    ->schema([

                                        Repeater::make("Buttons")
                                            ->relationship('buttons')
                                            ->collapsed()
                                            ->addable(false)
                                            ->schema([])
                                            ->orderColumn('weight_order')
                                            ->itemLabel(fn(array $state): ?string => $state['url'] ?? null)
                                            ->visible(fn(string $operation, $record): bool => $operation != 'create' && count($record->buttons))
                                            ->columnSpanFull()
                                            ->deleteAction(
                                                fn(Action $action) => $action->requiresConfirmation()
                                                    ->action(function (array $arguments, Repeater $component, Component $livewire): void {
                                                        $records = $component->getCachedExistingRecords();
                                                        $record = $records[$arguments['item']] ?? null;
                                                        if ($record) {
                                                            $record->delete();
                                                        }
                                                        $livewire->dispatch('refresh');

                                                    }),
                                            )
                                            ->extraItemActions([

                                                Action::make('edit')
                                                    ->label(__('Edit'))
                                                    ->icon('heroicon-m-pencil-square')
                                                    ->modalHeading(__('Edit Container'))
                                                    ->modalWidth('5xl')
                                                    ->schema(fn(Schema $schema, $get) => ButtonResource::formPopup($schema, $get("slug")))
                                                    ->fillForm(function (array $arguments, Repeater $component) {

                                                        $records = $component->getCachedExistingRecords();
                                                        $record = $records[$arguments['item']] ?? null;

                                                        $data = $record->toArray();

                                                        foreach ($record->getTranslationsArray() as $locale => $attributes) {
                                                            $data[$locale] = $attributes;
                                                        }

                                                        return $data;
                                                    })
                                                    ->action(function ($data, $record, array $arguments, Repeater $component, Component $livewire): void {
                                                        $records = $component->getCachedExistingRecords();
                                                        $record = $records[$arguments['item']] ?? null;
                                                        $record->update($data);
                                                        $livewire->refreshFormData(["Buttons"]);
                                                    }),

                                            ]),


                                        Actions::make([
                                            Action::make('addButton')
                                                ->label(__('Add Button'))
                                                ->modalHeading(__('Add Button'))
                                                ->modalWidth('5xl')
                                                ->schema(fn(Schema $schema, $get) => ButtonResource::formPopup($schema, $get('slug')))
                                                ->action(function ($data, Component $livewire, $record): void {
                                                    Button::create($data);
                                                    $livewire->dispatch('refresh');
                                                })
                                                ->visible(fn(array $state): bool => !empty($state["slug"])),

                                        ])
                                            ->columnSpanFull()
                                            ->alignCenter(),



                                    ])
                                    ->visible(fn(array $state): bool => !empty($state["slug"])),





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
                                    ->options(Bms::getStatusList())
                                    ->default(Bms::STATUS_PUBLISHED)
                                    ->native(false)
                                    ->selectablePlaceholder(false)
                                    ->notRegex('/<[^b][^r][^>]*>/')
                                    ->validationMessages([
                                        'not_regex' => 'HTML is invalid',
                                    ]),

                                Select::make('category')
                                    ->label(__('Category'))
                                    ->required()
                                    ->options(function () {
                                        $static = Bms::getCategoryListPlain();

                                        // $dynamic = DropdownList::query()
                                        //     ->where('category', DropdownList::BMS_CATEGORY)
                                        //     ->get()
                                        //     ->mapWithKeys(function ($item) {
                                        //         return [$item->slug => $item->lang->title ?? $item->slug];
                                        //     })
                                        //     ->toArray();

                                        return $static;

                                    })
                                    // ->relationship('category', 'title', fn(Builder $query) => $query->where('category', DropdownList::BMS_CATEGORY))
                                    // ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->title}")
                                    ->native(false)
                                    ->createOptionModalHeading('Create')
                                    // ->createOptionForm(fn(Form $form) => DropdownListResource::formPopup($form, DropdownList::BMS_CATEGORY))
                                    // ->createOptionUsing(function (array $data) {

                                        // $optionRecord = DropdownList::create($data);

                                        // return $optionRecord->slug;

                                    // })
                                    ->notRegex('/<[^b][^r][^>]*>/')
                                    ->validationMessages([
                                        'not_regex' => 'HTML is invalid',
                                    ]),
                                // !! if it's static category, you can use the following code
                                // \Filament\Forms\Components\Select::make('category')
                                //     ->label(__("Category"))
                                //     ->required()
                                //     ->options(Bms::getCategoryList())
                                //     ->native(false)
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


    public static function formPopup(Schema $schema, $params = [], $get): Schema
    {

        return $schema->components([

            TranslatableTabsNoArabic::make()
                ->columnSpan(2)
                ->localeTabSchema(fn(TranslatableTab $tab) => [

                    Grid::make(3)->schema([
                        Section::make()
                            ->columnSpan(2)
                            ->schema([
                                Hidden::make('module_id')
                                    ->default(
                                        $params['module_id'] instanceof Closure
                                        ? $params['module_id']($get)
                                        : $params['module_id'] ?? null
                                    ),

                                Hidden::make('module_class')
                                    ->default(
                                        $params['module_class'] instanceof Closure
                                        ? $params['module_class']($get)
                                        : $params['module_class'] ?? null
                                    ),
                                TextInput::make($tab->makeName('title'))
                                    ->label(__("Title[" . $tab->getLocale() . "]"))
                                    ->required()
                                    ->maxLength(510)
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


                                TextInput::make($tab->makeName('second_title'))
                                    ->label(__("Second Title[" . $tab->getLocale() . "]"))

                                    ->maxLength(255)

                                    ->notRegex('/<[^b][^r][^>]*>/')
                                    ->validationMessages([
                                        'not_regex' => 'HTML is invalid',
                                    ]),


                                Textarea::make($tab->makeName('brief'))
                                    ->label(__("Brief[" . $tab->getLocale() . "]"))
                                    ->maxLength(1000)
                                    ->rows(6)
                                    ->notRegex('/<[^b][^r][^>]*>/')
                                    ->validationMessages([
                                        'not_regex' => 'HTML is invalid',
                                    ]),

                                CustomCuratorPicker::make($tab->makeName("image_id"))
                                    ->label(__("Main Image[" . $tab->getLocale() . "]"))
                                    ->pathGenerator(DatePathGenerator::class)
                                    ->size(40)
                                    ->color('primary')
                                    ->outlined(true)
                                    ->size('md')
                                    ->constrained(true)
                                    ->listDisplay(false)
                                    ->orderColumn('order')
                                    ->multiple(false),





                                CustomCuratorPicker::make($tab->makeName("image_responsive_id"))
                                    ->label(__("Mobile Image[" . $tab->getLocale() . "]"))
                                    ->pathGenerator(DatePathGenerator::class)
                                    ->size(40)
                                    ->color('primary')
                                    ->outlined(true)
                                    ->size('md')
                                    ->constrained(true)
                                    ->orderColumn('order'),

                                TinyEditor::make($tab->makeName('content'))
                                    ->label(__("Content[" . $tab->getLocale() . "]"))
                                    ->showMenuBar()
                                    ->toolbarSticky(true)
                                    ->profile('with-bootstrap'),












                            ]),

                        Section::make()
                            ->columnSpan(1)
                            ->schema([


                                // Forms\Components\TextInput::make('url')
                                //     ->label(__("Url"))
                                //     ->maxLength(255)
                                //     ->notRegex('/<[^b][^r][^>]*>/')
                                //     ->validationMessages([
                                //         'not_regex' => 'HTML is invalid',
                                //     ]),

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


                                Select::make('category')
                                    ->label(__("Category"))
                                    ->required()
                                    ->options(function () {
                                        $static = Bms::getCategoryListPlain();

                                        // $dynamic = DropdownList::query()
                                        //     ->where('category', DropdownList::BMS_CATEGORY)
                                        //     ->get()
                                        //     ->mapWithKeys(function ($item) {
                                        //         return [$item->slug => $item->lang->title ?? $item->slug];
                                        //     })
                                        //     ->toArray();

                                        return $static ;
                                    })
                                    ->native(false)
                                    ->notRegex('/<[^b][^r][^>]*>/')
                                    ->validationMessages([
                                        'not_regex' => 'HTML is invalid',
                                    ])->default(
                                        $params['category'] instanceof Closure
                                        ? $params['category']($get)
                                        : $params['category'] ?? null
                                    ),


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
            ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('category', array_keys(Bms::getCategoryList())))
            ->columns([



                // CuratorColumn::make("image_id")
                //     ->label('image')
                //     ->size(40),


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



                FilamentUtility::publishedAtColumn(),


                SelectColumn::make('category')
                    ->label(__("Category"))
                    ->options(function () {
                        $static = Bms::getCategoryListPlain();

                        // $dynamic = DropdownList::query()
                        //     ->where('category', DropdownList::BMS_CATEGORY)
                        //     ->get()
                        //     ->mapWithKeys(function ($item) {
                        //         return [$item->slug => $item->lang->title ?? $item->slug];
                        //     })
                        //     ->toArray();

                        return $static ;
                    })
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
                    ->orderBy('weight_order', 'asc')
                    ->orderBy('published_at', 'desc');
            })
            ->striped()
            ->reorderable('weight_order')

            ->filters(

                static::renderFilterActions([
                    SelectFilter::make('category')
                        ->label(__('Filter by Category'))
                        ->options(Bms::getCategoryList())
                        ->searchable(),
                ]),
            )
            ->filtersLayout(FiltersLayout::AboveContent)

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
            ])
            ->filtersFormColumns(4);
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
