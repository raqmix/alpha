<?php

namespace App\Filament\Resources\ContactUsWebform;

use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use App\Classes\FilamentUtility;
use Filament\Actions\ExportAction;
use Filament\Support\Enums\Size;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ExportBulkAction;
use App\Filament\Resources\ContactUsWebformResource\Pages;

use CactusGalaxy\FilamentAstrotomic\Resources\Concerns\ResourceTranslatable;
use Filament\Resources\Resource;
use App\Filament\Exports\ContactUsWebformExporter;
use App\Filament\Resources\ContactUsWebform\Exports\Exporter;
use App\Filament\Resources\ContactUsWebform\Model\ContactUsWebform;
use App\Filament\Resources\ContactUsWebform\ResourcePages\ListItems;
use Filament\Tables;
use Filament\Tables\Table;


class ContactUsWebformResource extends Resource
{
    use ResourceTranslatable;

    protected static ?string $model = ContactUsWebform::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-s-phone';

    public static function getNavigationBadge(): ?string
    {
        return self::$model::count();
    }

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __("Contact Us Webform");
    }

    public static function getModelLabel(): string
    {
        return __("Contact Us Webform");
    }

    public static function getPluralLabel(): ?string
    {
        return __("Contact Us Webform");
    }

    public static function getPluralModelLabel(): string
    {
        return __("Contact Us Webform");
    }

    public static function getNavigationGroup(): ?string
    {
        return __("WebForms");
    }





    public static function form(Schema $schema): Schema
    {

        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([





                TextColumn::make('first_name')
                    ->label(__("First Name"))
                    ->searchable(),
                TextColumn::make('last_name')
                    ->label(__("Last Name"))
                    ->searchable(),


                TextColumn::make('email')
                    ->label(__("email"))
                    ->searchable(),

                TextColumn::make('phone')
                    ->label(__("phone"))
                    ->searchable(),

                TextColumn::make('company')
                    ->label(__("Company"))
                    ->searchable(),

                TextColumn::make('product')
                    ->label(__("Product of Interest"))
                    ->searchable(),

                TextColumn::make('message')
                    ->label(__("message"))
                    ->searchable(),


                FilamentUtility::createdAtColumn(),



            ])
            ->defaultSort('id', 'desc')
            ->striped()
            ->filters([])

            ->recordActions([

            ])
            ->headerActions([

                ExportAction::make()
                    ->exporter(Exporter::class)
                    ->chunkSize(500)
                    ->color("success")
                    ->icon("heroicon-o-inbox-arrow-down")
                    ->iconButton()
                    ->size(Size::Large),

            ])
            ->toolbarActions([
                BulkActionGroup::make([

                    ExportBulkAction::make()
                        ->exporter(Exporter::class)
                        ->color("success")
                        ->icon("heroicon-o-inbox-arrow-down")
                        ->label("Export Spacific Columns")
                        ->chunkSize(500),
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
        ];
    }
}
