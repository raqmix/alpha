<?php

namespace Outerweb\FilamentSettings;

use Illuminate\Support\ServiceProvider;

/**
 * Minimal replacement for the upstream package service provider. Only the
 * translation namespace is needed here — the page is rendered through
 * Filament v5's content schema, so the package ships no views.
 */
class FilamentSettingsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'filament-settings');
    }
}
