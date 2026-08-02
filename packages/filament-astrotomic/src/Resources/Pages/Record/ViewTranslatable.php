<?php

declare(strict_types=1);

namespace CactusGalaxy\FilamentAstrotomic\Resources\Pages\Record;

use Astrotomic\Translatable\Contracts\Translatable;
use CactusGalaxy\FilamentAstrotomic\Resources\Concerns\HasLocales;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin ViewRecord
 */
trait ViewTranslatable
{
    use HasLocales;

    protected function fillForm(): void
    {
        /** @var Model|Translatable $record */
        $record = $this->getRecord();

        $this->fillFormWithDataAndCallHooks($record, $this->mutateTranslatableData($record));
    }
}
