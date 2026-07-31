<?php

namespace App\Filament\Resources\Seo\Imports;

use App\Filament\Resources\Seo\Model\Seo;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer as ImportsImporter;
use Filament\Actions\Imports\Models\Import;

class Importer extends ImportsImporter
{
    protected static ?string $model = Seo::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('path')
                ->requiredMapping()
                ->rules(['max:255'])
                ->examples(['/test3', '/test']),

            ImportColumn::make('title:en')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255'])
                ->examples(['1042 kJ', '40g']),

            ImportColumn::make('title:ar')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255'])
                ->examples(['1042 kJ ar', '40g ar']),

            ImportColumn::make('description:en')
                ->requiredMapping()
                ->rules(['string', 'max:255'])
                ->examples(['red', 'green']),

            ImportColumn::make('description:ar')
                ->requiredMapping()
                ->rules(['string', 'max:255'])
                ->examples(['red', 'green']),

            ImportColumn::make('author:en')
                ->requiredMapping()
                ->rules(['string', 'max:255'])
                ->examples(['raqmix', 'green']),

            ImportColumn::make('author:ar')
                ->requiredMapping()
                ->rules(['string', 'max:255'])
                ->examples(['raqmix', 'green']),

            ImportColumn::make('keywords:en')
                ->requiredMapping()
                ->rules(['string', 'max:255'])
                ->examples(['raqmix', 'green']),

            ImportColumn::make('keywords:ar')
                ->requiredMapping()
                ->rules(['string', 'max:255'])
                ->examples(['raqmix', 'green']),


            ImportColumn::make('path')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255'])
                ->examples(['/blog/example-post', '/about']),

            ImportColumn::make('model_type')
                ->requiredMapping()
                ->rules(['string', 'max:255'])
                ->examples(['App\Filament\Resources\Blog\Model\Blog', 'App\Filament\Resources\Page\Model\Page']),

            ImportColumn::make('model_id')
                ->requiredMapping()
                ->rules(['number'])
                ->examples(['1', '2']),

            ImportColumn::make('robots')
                ->requiredMapping()
                ->rules(['string', 'max:255'])
                ->examples(['index, follow', 'noindex, nofollow']),


            ImportColumn::make('status')
                ->requiredMapping()
                ->integer()
                ->rules(['integer'])
                ->examples(['1-yes', '0-no'])
                ->ignoreBlankState(),



        ];
    }

    public function resolveRecord(): ?Seo
    {
        // return Seo::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Seo();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your Seo import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
