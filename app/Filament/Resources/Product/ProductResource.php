<?php

namespace App\Filament\Resources\Product;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use App\Classes\CustomPackage\CustomComponent\CustomCuratorPicker;
use App\Classes\FilamentUtility;
use App\Filament\Forms\Components\TranslatableTabsNoArabic;
use App\Filament\Resources\Product\Model\Product;
use App\Filament\Resources\Product\ResourcePages\Create;
use App\Filament\Resources\Product\ResourcePages\Edit;
use App\Filament\Resources\Product\ResourcePages\ListItems;
use App\Traits\AllColumnActionVisibility;
use App\Traits\CommonActionButtons;
use Awcodes\Curator\Components\Tables\CuratorColumn;
use Awcodes\Curator\PathGenerators\DatePathGenerator;
use CactusGalaxy\FilamentAstrotomic\Resources\Concerns\ResourceTranslatable;
use CactusGalaxy\FilamentAstrotomic\TranslatableTab;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ProductResource extends Resource
{
    use ResourceTranslatable, AllColumnActionVisibility, CommonActionButtons;

    protected static ?string $model = Product::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-archive-box';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return self::$model::count();
    }

    public static function getNavigationLabel(): string
    {
        return __('Products');
    }

    public static function getModelLabel(): string
    {
        return __('Product');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Products');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Products');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Media Center');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            TranslatableTabsNoArabic::make()
                ->columnSpan(2)
                ->localeTabSchema(fn (TranslatableTab $tab) => [
                    Grid::make(3)->schema([
                        Section::make()
                            ->columnSpan(2)
                            ->schema([
                                TextInput::make($tab->makeName('title'))
                                    ->label(__('Title[' . $tab->getLocale() . ']'))
                                    ->required($tab->getLocale() === 'en')
                                    ->maxLength(255),

                                TextInput::make($tab->makeName('hero_title'))
                                    ->label(__('Hero Title[' . $tab->getLocale() . ']'))
                                    ->maxLength(255),

                                TextInput::make('slug')
                                    ->label(__('Slug'))
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Generated Automatically')
                                    ->maxLength(255)
                                    ->readOnly(),

                                Textarea::make($tab->makeName('brief'))
                                    ->label(__('Brief[' . $tab->getLocale() . ']'))
                                    ->rows(3),

                                CustomCuratorPicker::make($tab->makeName('image_id'))
                                    ->label(__('Main Image[' . $tab->getLocale() . ']'))
                                    ->pathGenerator(DatePathGenerator::class)
                                    ->color('primary')
                                    ->outlined(true)
                                    ->size('md')
                                    ->constrained(true)
                                    ->listDisplay(false)
                                    ->orderColumn('order')
                                    ->multiple(false),

                                Repeater::make($tab->makeName('gallery_items'))
                                    ->label(__('Gallery[' . $tab->getLocale() . ']'))
                                    ->defaultItems(0)
                                    ->collapsed()
                                    ->itemLabel(fn (array $state): ?string => $state['alt'] ?? __('Gallery Item'))
                                    ->addActionLabel(__('Add Gallery Item'))
                                    ->schema([
                                        CustomCuratorPicker::make('image_id')
                                            ->label(__('Image'))
                                            ->pathGenerator(DatePathGenerator::class)
                                            ->color('primary')
                                            ->outlined(true)
                                            ->size('md')
                                            ->constrained(true)
                                            ->listDisplay(false)
                                            ->orderColumn('order')
                                            ->multiple(false),
                                        TextInput::make('alt')
                                            ->label(__('Alt Text')),
                                    ])
                                    ->columnSpanFull(),

                                Repeater::make($tab->makeName('description_sections'))
                                    ->label(__('Description Sections[' . $tab->getLocale() . ']'))
                                    ->defaultItems(0)
                                    ->collapsed()
                                    ->itemLabel(function (array $state): ?string {
                                        $type = $state['type'] ?? null;
                                        $heading = $state['heading'] ?? null;

                                        if ($heading && $type) {
                                            return $heading . ' (' . ucfirst($type) . ')';
                                        }

                                        if ($heading) {
                                            return $heading;
                                        }

                                        if ($type) {
                                            return __('Section') . ' (' . ucfirst($type) . ')';
                                        }

                                        return __('Description Section');
                                    })
                                    ->addActionLabel(__('Add Description Section'))
                                    ->schema([
                                        Select::make('type')
                                            ->label(__('Section Type'))
                                            ->options([
                                                'split' => __('Split Content'),
                                                'full' => __('Full Width Image'),
                                                'grid' => __('Feature Grid'),
                                            ])
                                            ->required()
                                            ->native(false),
                                        Select::make('image_position')
                                            ->label(__('Image Position'))
                                            ->options([
                                                'left' => __('Left'),
                                                'right' => __('Right'),
                                            ])
                                            ->native(false),
                                        TextInput::make('heading')
                                            ->label(__('Heading'))
                                            ->required(),
                                        Textarea::make('content')
                                            ->label(__('Content'))
                                            ->rows(5)
                                            ->columnSpanFull(),
                                        CustomCuratorPicker::make('image_id')
                                            ->label(__('Primary Image'))
                                            ->pathGenerator(DatePathGenerator::class)
                                            ->color('primary')
                                            ->outlined(true)
                                            ->size('md')
                                            ->constrained(true)
                                            ->listDisplay(false)
                                            ->orderColumn('order')
                                            ->multiple(false),
                                        CustomCuratorPicker::make('image_1_id')
                                            ->label(__('Grid Image 1'))
                                            ->pathGenerator(DatePathGenerator::class)
                                            ->color('primary')
                                            ->outlined(true)
                                            ->size('md')
                                            ->constrained(true)
                                            ->listDisplay(false)
                                            ->orderColumn('order')
                                            ->multiple(false),
                                        CustomCuratorPicker::make('image_2_id')
                                            ->label(__('Grid Image 2'))
                                            ->pathGenerator(DatePathGenerator::class)
                                            ->color('primary')
                                            ->outlined(true)
                                            ->size('md')
                                            ->constrained(true)
                                            ->listDisplay(false)
                                            ->orderColumn('order')
                                            ->multiple(false),
                                        CustomCuratorPicker::make('image_3_id')
                                            ->label(__('Grid Image 3'))
                                            ->pathGenerator(DatePathGenerator::class)
                                            ->color('primary')
                                            ->outlined(true)
                                            ->size('md')
                                            ->constrained(true)
                                            ->listDisplay(false)
                                            ->orderColumn('order')
                                            ->multiple(false),
                                    ])
                                    ->columnSpanFull(),

                                Repeater::make($tab->makeName('client_logos'))
                                    ->label(__('Client Logos[' . $tab->getLocale() . ']'))
                                    ->defaultItems(0)
                                    ->collapsed()
                                    ->itemLabel(fn (array $state): ?string => $state['label'] ?? __('Client Logo'))
                                    ->addActionLabel(__('Add Client Logo'))
                                    ->schema([
                                        TextInput::make('label')
                                            ->label(__('Label'))
                                            ->required(),
                                        TextInput::make('icon')
                                            ->label(__('Icon Class'))
                                            ->placeholder('fas fa-building'),
                                    ])
                                    ->columnSpanFull(),
                            ]),

                        Section::make()
                            ->columnSpan(1)
                            ->schema([
                                TextInput::make('sku')
                                    ->label(__('SKU'))
                                    ->maxLength(255),

                                DatePicker::make('published_at')
                                    ->label(__('Published At'))
                                    ->rules(['date_format:Y-m-d', 'regex:/^\d{4}-\d{2}-\d{2}$/'])
                                    ->default(Carbon::now())
                                    ->required(),

                                Select::make('status')
                                    ->label(__('Status'))
                                    ->required()
                                    ->options(self::$model::getStatusList())
                                    ->default(self::$model::STATUS_PUBLISHED)
                                    ->native(false)
                                    ->selectablePlaceholder(false),

                                Toggle::make('is_campaign')
                                    ->label(__('PROMOTE TO HOMEPAGE'))
                                    ->default(false)
                                    ->required()
                                    ->inline(false),

                                TextInput::make('weight_order')
                                    ->label(__('Weight Order'))
                                    ->required()
                                    ->integer()
                                    ->default(10),

                                Select::make('category_id')
                                    ->label(__('Category'))
                                    ->options(Product::getProductCategoryList())
                                    ->native(false)
                                    ->searchable(),

                                FilamentUtility::seoSection($tab, '/products/'),
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
                    ->label(__('Title'))
                    ->searchable(
                        isIndividual: false,
                        query: function (Builder $query, string $search): Builder {
                            return $query->whereHas('translations', function ($query) use ($search) {
                                $query->where('title', 'like', "%{$search}%")
                                    ->where('language', 'en');
                            });
                        }
                    ),
                CuratorColumn::make('image_id')->label('Image'),
                TextColumn::make('category.title')
                    ->label(__('Category'))
                    ->sortable(),
                TextColumn::make('sku')
                    ->label(__('SKU'))
                    ->toggleable(),
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
                    SelectFilter::make('category_id')
                        ->label(__('Category'))
                        ->options(Product::getProductCategoryList()),
                ]),
            )
            ->recordActions(static::renderTableActions())
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
        return [];
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
