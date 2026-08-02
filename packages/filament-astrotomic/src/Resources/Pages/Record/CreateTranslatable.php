<?php

declare(strict_types=1);

namespace CactusGalaxy\FilamentAstrotomic\Resources\Pages\Record;

use CactusGalaxy\FilamentAstrotomic\Resources\Concerns\HasLocales;
use Filament\Resources\Pages\CreateRecord;

/**
 * The upstream package overrode fillForm() here, but that override is now
 * identical to CreateRecord::fillForm() in Filament v5, so it is omitted.
 *
 * @mixin CreateRecord
 */
trait CreateTranslatable
{
    use HasLocales;
}
