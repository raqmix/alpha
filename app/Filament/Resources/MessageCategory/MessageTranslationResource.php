<?php

namespace App\Filament\Resources\MessageCategory;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\MessageCategory\Model\Message;
use App\Traits\CommonActionButtons;
use App\Filament\Forms\Components\TranslatableTabsNoArabic;
use CactusGalaxy\FilamentAstrotomic\Resources\Concerns\ResourceTranslatable;
use CactusGalaxy\FilamentAstrotomic\TranslatableTab;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MessageTranslationResource extends Resource
{
    use ResourceTranslatable, CommonActionButtons;

    protected static ?string $model = Message::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-globe-alt';

    protected static bool $shouldRegisterNavigation = false;


    public static function getModelLabel(): string
    {
        return __("admin.Messag Translation");
    }

    public static function getPluralLabel(): ?string
    {
        return __("admin.Messag Translations");
    }

    public static function getPluralModelLabel(): string
    {
        return __("admin.Messag Translations");
    }


    public static function form(Schema $schema): Schema
    {
        return $schema->components([

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
                            ->maxLength(500)
                            ->notRegex('/<[^b][^r][^>]*>/')
                            ->validationMessages([
                                'not_regex' => 'HTML is invalid',
                            ]),


                        TextInput::make($tab->makeName('translation'))
                            ->label(__("Translation[". $tab->getLocale()."]"))
                            ->autofocus()
                            ->required()
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


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
            ])

            ->filters(
                static::renderFilterActions(),
            )
            ->recordActions([
                //EditAction::make(),
                //DeleteAction::make(),

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

    public static function getPages(): array
    {
        return [
            //'index' => Pages\ListMessageTranslations::route('/{category_id}/translations'),
            //'create' => Pages\CreateMessageTranslation::route('/create'),
            //'edit' => Pages\EditMessageTranslation::route('/{record}/edit'),

        ];
    }
}
