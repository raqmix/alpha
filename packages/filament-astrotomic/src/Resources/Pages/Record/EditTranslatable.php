<?php

declare(strict_types=1);

namespace CactusGalaxy\FilamentAstrotomic\Resources\Pages\Record;

use Astrotomic\Translatable\Contracts\Translatable;
use CactusGalaxy\FilamentAstrotomic\Resources\Concerns\HasLocales;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin EditRecord
 */
trait EditTranslatable
{
    use HasLocales;

    protected function fillForm(): void
    {
        /** @var Model|Translatable $record */
        $record = $this->getRecord();

        // Filament v5 merges $extraData over $record->attributesToArray() and then
        // runs mutateFormDataBeforeFill()/hooks, so passing only the per-locale
        // translation arrays here reproduces the v3 fill order exactly.
        $this->fillFormWithDataAndCallHooks($record, $this->mutateTranslatableData($record));
    }
}
