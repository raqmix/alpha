<?php

namespace App\Filament\Resources\Audit;

use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ExportAction;
use Filament\Support\Enums\Size;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ExportBulkAction;
use App\Classes\FilamentUtility;
use App\Filament\Resources\Audit\Exports\Exporter;
use App\Filament\Resources\Audit\ResourcePages\ListItems;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use OwenIt\Auditing\Models\Audit;
use PepperFM\FilamentJson\Columns\JsonColumn;

class AuditResource extends Resource
{
    protected static ?string $model = Audit::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-s-pencil-square';

    public static function getNavigationBadge(): ?string
    {
        return self::$model::count();
    }

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __("Audit");
    }

    public static function getModelLabel(): string
    {
        return __("Audit");
    }

    public static function getPluralLabel(): ?string
    {
        return __("Audits");
    }

    public static function getPluralModelLabel(): string
    {
        return __("Audits");
    }

    public static function getNavigationGroup(): ?string
    {
        return __("Setting");
    }





    public static function form(Schema $schema): Schema
    {

        return $schema->columns(1)->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([



                TextColumn::make('user.username')
                    ->label(__("User"))
                    ->searchable(),


                TextColumn::make('event')
                    ->label(__("Event"))
                    ->searchable(),

                TextColumn::make('auditable_type')
                    ->label(__("Model"))
                    ->searchable(),

                TextColumn::make('auditable_type')
                    ->label(__("Model"))
                    ->searchable(),


                TextColumn::make('auditable_id')
                    ->label(__("Model ID"))
                    ->searchable(),


                JsonColumn::make('old_values')
                    ->label(__("Old Values"))
                    ->getStateUsing(fn ($record) => $record->old_values ?: [null])
                    ->alignStart()
                    ->extraCellAttributes(['class' => 'pe-5']),

                JsonColumn::make('new_values')
                    ->label(__("New Values"))
                    ->getStateUsing(fn ($record) => $record->new_values ?: [null])
                    ->alignStart()
                    ->extraCellAttributes(['class' => 'ps-5']),


                FilamentUtility::createdAtColumn(),
                FilamentUtility::updatedAtColumn(),

                TextColumn::make('url')
                    ->label(__("Url")),


                TextColumn::make('ip_address')
                    ->label(__("IP Address")),


                TextColumn::make('user_agent')
                    ->label(__("User Agent")),



            ])
            ->defaultSort('id', 'desc')
            ->striped()

            ->filters([


            ])

            ->recordActions([])
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
