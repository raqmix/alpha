<?php

namespace App\Filament\Resources\MessageCategory\RelationManagers;

use Filament\Schemas\Schema;
use App\Filament\Forms\Components\TranslatableTabsNoArabic;
use CactusGalaxy\FilamentAstrotomic\TranslatableTab;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\BulkActionGroup;
use App\Filament\Resources\MessageCategory\Exports\MessageExporter;
use App\Filament\Resources\MessageCategory\Imports\MessageImporter;
use App\Filament\Resources\MessageCategory\Model\Message;
use App\Traits\AllColumnActionVisibility;
use App\Traits\CommonActionButtons;
use CactusGalaxy\FilamentAstrotomic\Resources\RelationManagers\Concerns\TranslatableRelationManager;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MessagesRelationManager extends RelationManager
{
    use TranslatableRelationManager, CommonActionButtons, AllColumnActionVisibility;

    protected static string $relationship = 'messages';

    protected static ?string $model = Message::class;

    public function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([

            TranslatableTabsNoArabic::make()
            ->columnSpan(2)
            ->localeTabSchema(fn (TranslatableTab $tab) => [

                Grid::make(3)->schema([
                    Section::make()
                    ->columnSpan(2)
                    ->schema([

                        TextInput::make('message')
                            ->label(__("Message"))
                            ->autofocus()
                            ->required()
                            ->maxLength(255)
                            ->notRegex('/<[^b][^r][^>]*>/')
                            ->validationMessages([
                                'not_regex' => 'HTML is invalid',
                            ]),


                        TextInput::make($tab->makeName('translation_value'))
                            ->label(__("Translation Value[". $tab->getLocale()."]"))
                            ->autofocus()
                            ->maxLength(255)
                            ->notRegex('/<[^b][^r][^>]*>/')
                            ->validationMessages([
                                'not_regex' => 'HTML is invalid',
                            ]),

                    ]),

                    Section::make()
                    ->columnSpan(1)
                    ->schema([

                        Select::make('category_id')
                            ->label(__("Category"))
                            ->required()
                            ->relationship(name: 'category', titleAttribute: 'title')
                            ->native(false)
                            ->selectablePlaceholder(false)
                            ->validationMessages([
                                'not_regex' => 'HTML is invalid',
                            ]),

                    ])
                    ->hidden(fn (string $operation): bool => ($operation == "create") )



                ])
            ])

        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('message')
            ->columns([

                TextColumn::make('message')
                ->label(__("Message"))
                ->searchable(),

                TextColumn::make('translation_value')
                ->label(__("Translation Value"))
                ->searchable(isIndividual: false,
                    query: function (Builder $query, string $search): Builder {
                        return $query
                            ->whereHas("translations", function($query) use($search){
                                $query->where('translation_value', 'like', "%{$search}%")
                                ->where("language","en");
                            });
                    }
                ),

                TextColumn::make('translation_value:ar')
                ->label(__("Translation Value Ar"))
                ->searchable(isIndividual: false,
                    query: function (Builder $query, string $search): Builder {
                        return $query
                            ->whereHas("translations", function($query) use($search){
                                $query->where('translation_value', 'like', "%{$search}%")
                                ->where("language","ar");
                            });
                    }
                ),

                SelectColumn::make('category_id')
                    ->label(__("Category"))
                    ->options(Message::getCategoryList())
                    ->alignCenter()
                    ->selectablePlaceholder(false),

            ])
            ->defaultSort("id","desc")
            ->striped()
            ->filters(
                static::renderFilterActions(),
            )
            ->headerActions(

                static::renderHeaderActions(MessageImporter::class, MessageExporter::class,
                    [
                        CreateAction::make()->createAnother(false)
                    ]
                ),
            )
            ->recordActions(
                static::renderTableActions(),
            )
            ->toolbarActions([
                BulkActionGroup::make(
                    static::renderBulkActions(MessageExporter::class)

                ),
            ]);
    }
}
