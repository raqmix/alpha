<?php

namespace App\Classes;

use Filament\Actions\Action;
use Illuminate\Support\HtmlString;
use Filament\Schemas\Components\Actions;
use Carbon\Carbon;
use Filament\Schemas\Components\Section;
use Livewire\Component;
use Filament\Schemas\Schema;
use App\Classes\CustomPackage\CustomComponent\CustomCuratorPicker;
use App\Filament\Resources\Seo\Model\Seo;
use Awcodes\Curator\PathGenerators\DatePathGenerator;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Forms\Components\Repeater;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Cache;

class FilamentUtility
{

    //buttons
    public static function isPreview()
    {
        $adminPath= Cache::rememberForever( "filament_path", function () {
                        return setting("general.filament_path", "admin") ?? "admin";
                    });
        $request = Request::create(url()->previous());
        $isAdmin = isset($request->segments()[0])? $request->segments()[0] : null;
        return $isAdmin === $adminPath;
    }

    public static function previewButton($frontroute, $tab, $record)
    {

        $buttons =[];
        if($record)
        {
            $buttons[] =
            Action::make( $tab->makeName('preview'))
            ->label(  __('admin.Preview'))
            ->icon('heroicon-o-eye')
            ->color('warning')
            ->modalWidth('5xl')
            ->modalContent(function ($record) use($frontroute, $tab) {
                $previewUrl = route($frontroute, ["locale"=>$tab->getLocale(), "slug"=>$record->translate($tab->getLocale())->slug]);
                return new HtmlString(
                    '<iframe src="' . $previewUrl . '"
                            width="100%"
                            height="600"
                            frameborder="0"
                            class="rounded-lg">
                    </iframe>'
                );
            })
            ->modalHeading(false)
            ->modalContentFooter(null)
            ->modalSubmitAction(false)
            ->modalCancelActionLabel(false)
            ->modalWidth('7xl');

        }

        return Actions::make($buttons)
        ->visible(fn (string $operation): bool => $operation != 'create' );



    }














    //table columns
    public static function publishedAtColumn(): TextColumn
    {
        return  TextColumn::make('published_at')
                ->label(__("Published At"))
                ->searchable()
                ->alignCenter()
                ->formatStateUsing(fn ($state) => Carbon::parse($state)->setTimezone(setting("general.time_zone"))->format(setting("general.date_format") .' '. setting("general.time_format")));
    }

    public static function statusColumn($model): ToggleColumn
    {
        return  ToggleColumn::make('status')
                ->label(__("Status"))
                ->alignCenter()
                ->visible( static::toggleColumnVisibility($model) && !auth()->user()->hasRole('maker') );
    }

    public static function statusColumnArabic($model): ToggleColumn
    {
        return  ToggleColumn::make('status:ar')
                ->label(__("Arabic Status"))
                ->alignCenter()
                ->visible( static::toggleColumnVisibility($model) && !auth()->user()->hasRole('maker') );
    }


    protected static function toggleColumnVisibility($model)
    {
        return fn() => auth()->user()->can('update_'. strtolower (preg_replace('/([A-Z])/', '::$1', lcfirst( class_basename($model) ) ) ) ) ;
    }

    public static function createdAtColumn(): TextColumn
    {
        return  TextColumn::make('created_at')
                ->label(__("Created At"))
                ->searchable()
                ->alignCenter()
                ->formatStateUsing(fn ($state) => Carbon::parse($state)->setTimezone(setting("general.time_zone"))->format(setting("general.date_format") .' '. setting("general.time_format")));
    }

    public static function updatedAtColumn(): TextColumn
    {
        return  TextColumn::make('updated_at')
                ->label(__("Updated At"))
                ->searchable()
                ->alignCenter()
                ->formatStateUsing(fn ($state) => Carbon::parse($state)->setTimezone(setting("general.time_zone"))->format(setting("general.date_format") .' '. setting("general.time_format")));
    }


    public static function creatorColumn(): TextColumn
    {
        return  TextColumn::make('creator.username')
                ->label(__("Creator"))
                ->searchable()
                ->alignCenter();
    }


    public static function updaterColumn(): TextColumn
    {
        return TextColumn::make('updater.username')
                ->label(__("Updater"))
                ->searchable()
                ->alignCenter();
    }

    public static function headerImageSection($tab, $fronturl)
    {
        return
            Section::make(__("Header Image"))
                ->relationship('headerImage')
                ->collapsed()
                ->schema([

                    CustomCuratorPicker::make($tab->makeName("image_id"))
                        ->label(__("Header Image[" . $tab->getLocale() . "]"))
                        ->pathGenerator(DatePathGenerator::class)
                        ->size(40)
                        ->color('primary')
                        ->outlined(true)
                        ->size('md')
                        ->constrained(true)
                        ->listDisplay(false)
                        ->orderColumn('order')
                        ->multiple(false),

                    CustomCuratorPicker::make($tab->makeName("mobile_image_id"))
                        ->label(__("Mobile Header Image[" . $tab->getLocale() . "]"))
                        ->pathGenerator(DatePathGenerator::class)
                        ->size(40)
                        ->color('primary')
                        ->outlined(true)
                        ->size('md')
                        ->constrained(false)
                        ->orderColumn('order'),

                    TextInput::make($tab->makeName('title'))
                        ->label(__("Header Image Title[" . $tab->getLocale() . "]"))
                        ->maxLength(255)
                        ->notRegex('/<[^b][^r][^>]*>/')
                        ->validationMessages([
                            'not_regex' => 'HTML is invalid',
                        ]),

                    TextInput::make($tab->makeName('header_image_brief'))
                        ->label(__("Header Image Brief[" . $tab->getLocale() . "]"))
                        ->maxLength(255)
                        ->notRegex('/<[^b][^r][^>]*>/')
                        ->validationMessages([
                            'not_regex' => 'HTML is invalid',
                        ]),

                    ColorPicker::make($tab->makeName('header_image_title_color'))
                        ->label(__("Header Image Color[" . $tab->getLocale() . "]"))
                        ->notRegex('/<[^b][^r][^>]*>/')
                        ->validationMessages([
                            'not_regex' => 'HTML is invalid',
                        ]),


                    Hidden::make('path')->default($fronturl),
                    Hidden::make('status')->default("1"),

                ]);

    }


    public static function seoSection($tab, $fronturl)
    {
        return
        Section::make(__("SEO"))
        ->relationship(
            'seo',
            condition: fn (?array $state): bool =>
            filled($state['robots'] ?? null) ||
            collect(config('translatable.locales'))
            ->contains(function ($locale) use ($state) {
                $fields = $state[$locale] ?? [];
                return collect(['title', 'description', 'author', 'keywords', 'image_id'])
                    ->contains(fn ($key) => !blank($fields[$key] ?? null));
            }),
        )
        ->collapsible()
        // ->collapsible()
        // ->persistCollapsed()
        ->persistCollapsed()
        ->schema([


            TextInput::make($tab->makeName('title'))
                ->label(__("SEO Title[". $tab->getLocale()."]") )
                ->requiredIfAccepted("robots")
                ->maxLength(255)
                ->notRegex('/<[^b][^r][^>]*>/')
                ->validationMessages([
                    'not_regex' => 'HTML is invalid',
            ]),

            Textarea::make($tab->makeName('description'))
                ->label(__("SEO Description[". $tab->getLocale()."]") )
                ->maxLength(255)
                ->notRegex('/<[^b][^r][^>]*>/')
                ->validationMessages([
                    'not_regex' => 'HTML is invalid',
            ]),


            TextInput::make($tab->makeName('author'))
                ->label(__("SEO Author[". $tab->getLocale()."]") )
                ->maxLength(255)
                ->notRegex('/<[^b][^r][^>]*>/')
                ->validationMessages([
                    'not_regex' => 'HTML is invalid',
            ]),


            Textarea::make($tab->makeName('keywords'))
                ->label(__("Keywords[". $tab->getLocale()."]") )
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


            CustomCuratorPicker::make($tab->makeName("image_id"))
                ->label(__("SEO OG Image[". $tab->getLocale()."]"))
                ->pathGenerator(DatePathGenerator::class)
                ->size(40)
                ->color('primary')
                ->outlined(true)
                ->size('md')
                ->constrained(true)
                ->listDisplay(false)
                ->orderColumn('order')
            ->multiple(false),


        ])
        ->mutateRelationshipDataBeforeCreateUsing(function (array $data, $component, $record, $get) use($fronturl): array {
            //dd($record, $component->getState());
            $data['path'] = $fronturl . $record->slug;
            $data['status'] = Seo::STATUS_PUBLISHED;

            return $data;
        })
        ->mutateRelationshipDataBeforeSaveUsing(function (array $data, $component, $record, $get) use($fronturl): array {
            //dd($record, $component->getState(), $get("slug"));
            $slug = $get("slug") == ""? $record->slug: $get("slug"); //we have issue if slug will be created from boot we need one more save
            $data['path'] = $fronturl . $slug;
            $data['status'] = Seo::STATUS_PUBLISHED;

            return $data;
        });

    }















    //form components

        //basic components
        public static function statusInput($model, $tab=null)
        {
            if($tab)
            {
                return
                    Select::make($tab->makeName('status'))
                    ->label(__("Status"). "[". $tab->getLocale()."]")
                    ->required()
                    ->options($model::getStatusList())
                    ->default($model::STATUS_PUBLISHED)
                    ->native(false)
                    ->selectablePlaceholder(false)
                    ->notRegex('/<[^b][^r][^>]*>/')
                    ->validationMessages([
                        'not_regex' => 'HTML is invalid',
                    ])
                    ->visible(fn () => !auth()->user()?->hasRole('maker'));
            }
            else
            {
                return
                    Select::make('status')
                    ->label(__("Status"))
                    ->required()
                    ->options($model::getStatusList())
                    ->default($model::STATUS_PUBLISHED)
                    ->native(false)
                    ->selectablePlaceholder(false)
                    ->notRegex('/<[^b][^r][^>]*>/')
                    ->validationMessages([
                        'not_regex' => 'HTML is invalid',
                    ])
                    ->visible(fn () => !auth()->user()?->hasRole('maker'));
            }



        }


        public static function selectWithCreate($inputName, $inputLabel, $relation, $columnToDisplay, $queryWithRelation, $createForm, $createModel, $columnToSave, $isMultiple=false, $live=true , $required=true, $searchable=false )
        {
            return

            Select::make($inputName)
                ->label(__($inputLabel))
                ->required($required)
                ->multiple($isMultiple)
                ->relationship($relation, $columnToDisplay, $queryWithRelation )
                ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->$columnToDisplay}")
                ->native(false)
                ->searchable($searchable)
                ->live($live)
                ->createOptionModalHeading('Create')
                ->createOptionForm($createForm)
                ->createOptionUsing(function (array $data) use($createModel, $columnToSave) {

                    $optionRecord = $createModel::create($data);

                    return $optionRecord->$columnToSave;

                })
                ->createOptionAction(function (Action $action) use($createModel) {
                    return $action
                        ->visible(fn () => auth()->user()->can('create', $createModel))
                        ->modalFooterActions([
                            $action->getModalSubmitAction(),
                            $action->getModalCancelAction(),
                        ]);
                })->preload();
        }





        //repeater
        public static function genericRepeater($repeaterName, $relation, $schemaRepeater , $orderColumn, $resource, $formPopup, $formParameters)
        {

            return
            Repeater::make(__($repeaterName))
            ->relationship($relation)
            ->collapsed()
            ->addable(false)
            ->schema($schemaRepeater)
            ->orderColumn($orderColumn)
            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
            ->visible(fn (string $operation, $record): bool => $operation != 'create' && $record && count($record->{$relation}))
            ->columnSpanFull()
            ->deleteAction(
                fn (Action $action) => $action->requiresConfirmation()
                ->action(function (array $arguments, Repeater $component, Component $livewire): void {
                    $records = $component->getCachedExistingRecords();
                    $record = $records[$arguments['item']] ?? null;
                    if($record){
                        $record->delete();
                    }
                    $livewire->dispatch('refresh');

                }),
            )
            ->extraItemActions([

                Action::make('toggle_status')
                ->label(__('Toggle Status'))
                ->icon(function (array $arguments, Repeater $component, Component $livewire) use($repeaterName) {
                    $records = $component->getCachedExistingRecords();
                    $record = $records[$arguments['item']] ?? null;
                return $record->status? 'fas-toggle-on' : 'fas-toggle-off';
                })
                ->color(function (array $arguments, Repeater $component, Component $livewire) use($repeaterName) {
                    $records = $component->getCachedExistingRecords();
                    $record = $records[$arguments['item']] ?? null;
                return $record->status? 'success' : 'warning';
                })
                ->action(function (array $arguments, Repeater $component, Component $livewire) use($repeaterName) {
                    $records = $component->getCachedExistingRecords();
                    $record = $records[$arguments['item']] ?? null;

                    if ($record) {
                        $record->update(['status'=>!$record->status]);
                        if (method_exists($livewire, 'refreshFormData')) {
                            $livewire->refreshFormData([$repeaterName]);
                        }
                        $livewire->dispatch('refresh');
                    }
                })
                ->requiresConfirmation()
                ->modalHeading(__('Toggle Status Confirmation')),

                Action::make('edit')
                ->label(__('Edit'))
                ->icon('heroicon-m-pencil-square')
                ->modalHeading(__('Edit Container'))
                ->modalWidth('5xl')
                ->schema(fn (Schema $schema, $get) => $resource::$formPopup($schema, $formParameters, $get))
                ->fillForm(function (array $arguments, Repeater $component) {

                    $records = $component->getCachedExistingRecords();
                    $record = $records[$arguments['item']] ?? null;

                    $data = $record->toArray();

                    foreach ($record->getTranslationsArray() as $locale => $attributes) {
                        $data[$locale] = $attributes;
                    }

                    return $data;
                })
                ->action(function ($data, $record, array $arguments, Repeater $component, Component $livewire) use($repeaterName): void {
                    $records = $component->getCachedExistingRecords();
                    $record = $records[$arguments['item']] ?? null;
                    $record->update($data);
                    if (method_exists($livewire, 'refreshFormData')) {
                        $livewire->refreshFormData([$repeaterName]);
                    }
                }),




            ]);

        }

        public static function genericAddButtonRepeater($buttonName, $buttonLabel, $resource, $formPopup, $formParameters, $model , $repeaterName)
        {

            return
            Actions::make([
                Action::make($buttonName)
                ->label(__($buttonLabel))
                ->modalHeading(__($buttonLabel))
                ->modalWidth('5xl')
                ->schema(fn (Schema $schema, $get) => $resource::$formPopup($schema, $formParameters, $get  ))
                ->action(function ($data, $livewire, $record) use($model, $repeaterName): void {
                    $model::create($data);
                    if (method_exists($livewire, 'refreshFormData')) {
                        if($livewire)
                        {
                            $livewire->refreshFormData([$repeaterName]);
                        }

                    }
                    $livewire->dispatch('refresh');
                    Notification::make()
                        ->title('Created Successfully')
                        ->success()
                        ->send();
                    $livewire->dispatch('refresh');
                }),

            ])
            ->columnSpanFull()
            ->alignCenter();

        }








    //pages routes
    public static function defaultPages(string $namespace, $newRoutes=[]): array
    {

        $defaultRoutes =
        [
            'index' => static::resolvePageClass($namespace, 'ListItems')::route('/'),
            'create' => static::resolvePageClass($namespace, 'Create')::route('/create'),
            'edit' => static::resolvePageClass($namespace, 'Edit')::route('/{record}/edit'),
            'view-revision' => static::resolvePageClass($namespace, 'ViewRevision')::route('/{record}/view-revision'),
        ];

        return array_merge($defaultRoutes, $newRoutes);
    }

    protected static function resolvePageClass(string $namespace, string $class): string
    {
        return $namespace . '\\' . $class;
    }


    // !! Works without translations
    public static function genericRepeater2($repeaterName, $relation, $schemaRepeater, $orderColumn, $resource, $formPopup, $formParameters)
    {

        return
            Repeater::make(__($repeaterName))
                ->relationship($relation)
                ->collapsed(true, false)
                ->addable(false)
                ->schema($schemaRepeater)
                ->orderColumn($orderColumn)
                ->itemLabel(fn(array $state): ?string => $state['title'] ?? $state['day'])
                ->visible(fn(string $operation, $record): bool => $operation != 'create' && $record && count($record->{$relation}))
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
                        ->schema(fn(Schema $schema, $get) => $resource::$formPopup($schema, $formParameters, $get))
                        ->fillForm(function (array $arguments, Repeater $component) {

                            $records = $component->getCachedExistingRecords();
                            $record = $records[$arguments['item']] ?? null;

                            $data = $record->toArray();

                            return $data;
                        })
                        ->action(function ($data, $record, array $arguments, Repeater $component, Component $livewire) use ($repeaterName): void {
                            $records = $component->getCachedExistingRecords();
                            $record = $records[$arguments['item']] ?? null;
                            $record->update($data);
                            if (method_exists($livewire, 'refreshFormData')) {
                                $livewire->refreshFormData([$repeaterName]);
                            }
                        }),




                ]);

    }

    public static function genericRepeater3($repeaterName, $relation, $schemaRepeater, $orderColumn, $resource, $formPopup, $formParameters)
    {

        return
            Repeater::make(__($repeaterName))
                ->relationship($relation)
                ->collapsed(true, false)
                ->addable(false)
                ->schema($schemaRepeater)
                ->orderColumn($orderColumn)
                ->itemLabel(fn(array $state): ?string => $state['title_en'] ?? $state['title_ar'])
                ->visible(fn(string $operation, $record): bool => $operation != 'create' && $record && count($record->{$relation}))
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
                        ->schema(fn(Schema $schema, $get) => $resource::$formPopup($schema, $formParameters, $get))
                        ->fillForm(function (array $arguments, Repeater $component) {

                            $records = $component->getCachedExistingRecords();
                            $record = $records[$arguments['item']] ?? null;

                            $data = $record->toArray();

                            return $data;
                        })
                        ->action(function ($data, $record, array $arguments, Repeater $component, Component $livewire) use ($repeaterName): void {
                            $records = $component->getCachedExistingRecords();
                            $record = $records[$arguments['item']] ?? null;
                            $record->update($data);
                            if (method_exists($livewire, 'refreshFormData')) {
                                $livewire->refreshFormData([$repeaterName]);
                            }
                        }),




                ]);

    }


}
