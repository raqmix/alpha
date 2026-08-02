<?php

namespace App\Filament\Resources\Blog;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
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
use App\Filament\Resources\Blog\Model\Blog;
use App\Filament\Resources\Blog\ResourcePages\Create;
use App\Filament\Resources\Blog\ResourcePages\Edit;
use App\Filament\Resources\Blog\ResourcePages\ListItems;
use App\Filament\Forms\Components\TranslatableTabsNoArabic;
use CactusGalaxy\FilamentAstrotomic\Resources\Concerns\ResourceTranslatable;
use CactusGalaxy\FilamentAstrotomic\TranslatableTab;
use App\Traits\CommonActionButtons;

use App\Traits\AllColumnActionVisibility;
use Awcodes\Curator\Components\Tables\CuratorColumn;
use Awcodes\Curator\PathGenerators\DatePathGenerator;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use AmidEsfahani\FilamentTinyEditor\TinyEditor;

class BlogResource extends Resource
{
    use ResourceTranslatable, AllColumnActionVisibility, CommonActionButtons;

    protected static ?string $model = Blog::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-newspaper';


    public static function getNavigationBadge(): ?string
    {
        return self::$model::count();
    }



    protected static ?int $navigationSort = 2;


    public static function getNavigationLabel(): string
    {
        return __("Blog");
    }

    public static function getModelLabel(): string
    {
        return __("Blog");
    }

    public static function getPluralLabel(): ?string
    {
        return __("Blog");
    }

    public static function getPluralModelLabel(): string
    {
        return __("Blog");
    }

    public static function getNavigationGroup(): ?string
    {
        return __("Media Center");
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
                                Textarea::make($tab->makeName('brief'))
                                    ->label(__("Brief[" . $tab->getLocale() . "]"))
                                    // ->maxLength(255)
                                    ->rows(3)
                                    ->notRegex('/<[^b][^r][^>]*>/')
                                    ->validationMessages([
                                        'not_regex' => 'HTML is invalid',
                                    ]),

                                CustomCuratorPicker::make($tab->makeName('image_id'))
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

                                TinyEditor::make($tab->makeName('content'))
                                    ->label(__("Content[" . $tab->getLocale() . "]"))
                                    ->showMenuBar()
                                    ->toolbarSticky(true)
                                    ->profile('with-bootstrap'),

                                // FilamentUtility::selectWithCreate(
                                //     'servicesCenters',
                                //     __("Specialized Centers"),
                                //     'servicesCenters',
                                //     'title',
                                //     fn(Builder $query) => $query->joinRelationship("translations"),
                                //     fn(Form $form) => SpecializedCenterResource::form($form),
                                //     Service::class,
                                //     "id",
                                //     true,
                                //     true,
                                //     false
                                // ),




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


                                Toggle::make('is_campaign')
                                    ->label(__("PROMOTE TO HOMEPAGE"))
                                    ->default(false)
                                    ->required()
                                    ->inline(false),


                                TextInput::make('weight_order')
                                    ->label(__("Weight Order"))
                                    ->required()
                                    ->integer()
                                    ->default(10)
                                    ->notRegex('/<[^b][^r][^>]*>/')
                                    ->validationMessages([
                                        'not_regex' => 'HTML is invalid',
                                    ]),

                                Select::make('category_id')
                                    ->label(__("Category"))
                                    ->options(Blog::getBlogCategoryList())
                                    ->native(false)
                                    ->searchable(),

                                    // reading_time
                                TextInput::make('reading_time')
                                    ->label(__("Reading Time (Minutes)"))
                                    ->integer()
                                    ->default(5)
                                    ->notRegex('/<[^b][^r][^>]*>/'),


                                FilamentUtility::seoSection($tab, '/blog/'),
                                // FilamentUtility::headerImageSection($tab, '/blog/')

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
                CuratorColumn::make("image_id")->label('Image'),

                TextColumn::make('category.title')
                    ->label(__("Category"))
                    ->sortable(),

                // Tables\Columns\TextColumn::make('title:en')
                // ->label(__("title En"))
                // ->searchable(isIndividual: false,
                //     query: function (Builder $query, string $search): Builder {
                //         return $query
                //             ->whereHas("translations", function($query) use($search){
                //                 $query->where('title', 'like', "%{$search}%")
                //                 ->where("language","ar");
                //             });
                //     }
                // ),



                // \App\Classes\FilamentUtility::publishedAtColumn(),


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
                        ->label(__("Category"))
                        ->options(Blog::getBlogCategoryList()),
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
