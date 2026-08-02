<?php

namespace App\Traits;

use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ImportAction;
use Filament\Support\Enums\Size;
use Filament\Actions\ExportAction;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ExportBulkAction;
use App\Filament\Jobs\CustomImportCsv;
use App\Models\User;
use App\Traits\AllColumnActionVisibility;
use Filament\Tables;
use Illuminate\Database\Eloquent\Collection;

trait CommonActionButtons
{

    public static function renderFilterActions($customActions = [])
    {
        $defaultActions =
        [
            TrashedFilter::make(),

            SelectFilter::make('created_by')
            ->options(User::getAllList()),

            SelectFilter::make('updated_by')
                ->options(User::getAllList()),
        ];

        return array_merge($customActions, $defaultActions);
    }


    public static function renderTableActions($customActions = [])
    {
        $defaultActions =
        [
            EditAction::make()->iconButton(),
            DeleteAction::make()->iconButton(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];

        return array_merge($customActions, $defaultActions);
    }



    public static function renderHeaderActions($importer, $exporter, $customActions = [])
    {
        $defaultActions =
        [
            ImportAction::make()
                ->importer($importer)
                ->job(CustomImportCsv::class)
                ->options([
                    'updateExisting' => true,
                ])
                ->chunkSize(500)
                ->color("warning")
                ->icon("heroicon-s-document-arrow-up")
                ->iconButton()
                ->size(Size::Large)
                ->visible( static::importActionVisibility() ),

            ExportAction::make()
                ->exporter($exporter)
                ->chunkSize(500)
                ->color("success")
                ->icon("heroicon-o-inbox-arrow-down")
                ->iconButton()
                ->size(Size::Large)
                ->visible( static::exportActionVisibility() ),
        ];

        return array_merge($customActions, $defaultActions);
    }

    public static function renderBulkActions($exporter, $customActions = [])
    {

        $defaultActions =
        [

            BulkAction::make('publish')
                ->action(fn (Collection $records) => static::BulkActivate($records))
                ->deselectRecordsAfterCompletion()
                ->requiresConfirmation()
                ->color('success')
                ->icon('fas-toggle-on')
                ->visible( static::toggleColumnVisibility() ),

            BulkAction::make('pending')
                ->action(fn (Collection $records) => static::BulkDeactivate($records))
                ->deselectRecordsAfterCompletion()
                ->requiresConfirmation()
                ->color('warning')
                ->icon('fas-toggle-off')
                ->visible( static::toggleColumnVisibility() ),

            DeleteBulkAction::make(),
            ForceDeleteBulkAction::make(),
            RestoreBulkAction::make(),

            ExportBulkAction::make()
                ->exporter($exporter)
                ->color("success")
                ->icon("heroicon-o-inbox-arrow-down")
                ->label("Export Spacific Columns")
                ->chunkSize(500)
                ->visible( static::exportActionVisibility() ),
        ];

        return array_merge($customActions, $defaultActions);

    }


    public static function BulkActivate($records)
    {
        if (count(($records)) )
        {
            $model = get_class($records[0]);
            $ids = $records->pluck('id');
            $model::whereIn("id",$ids)->update(["status"=>true]);
        }


    }

    public static function BulkDeactivate($records)
    {
        if (count(($records)) )
        {
            $model = get_class($records[0]);
            $ids = $records->pluck('id');
            $model::whereIn("id",$ids)->update(["status"=>false]);
        }
    }


}

