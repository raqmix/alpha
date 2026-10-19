<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Outerweb\Settings\Models\Setting;

/**
 * Give the reCAPTCHA settings a starting point: switched on, with Google's
 * universal test keys, so every webform has a working checkbox before anyone
 * has registered a real key pair.
 *
 * Existing values are never overwritten, so this is safe to run on a site that
 * already has its own keys saved.
 */
return new class extends Migration
{
    public function up(): void
    {
        $table = config('settings.database_table_name', 'settings');

        if (! Schema::hasTable($table)) {
            return;
        }

        $defaults = [
            'general.google_recaptcha_enabled' => true,
            'general.google_recaptcha_site_key' => config('recaptcha.universal.site_key'),
            'general.google_recaptcha_secret' => config('recaptcha.universal.secret_key'),
        ];

        foreach ($defaults as $key => $value) {
            $existing = Setting::query()->where('key', $key)->first();

            // Absent, or present but never filled in.
            if (is_null($existing) || blank($existing->value)) {
                Setting::set($key, $value);
            }
        }

        cache()->forget(config('settings.cache_key'));
    }

    public function down(): void
    {
        $table = config('settings.database_table_name', 'settings');

        if (! Schema::hasTable($table)) {
            return;
        }

        Setting::query()
            ->where('key', 'general.google_recaptcha_enabled')
            ->delete();

        cache()->forget(config('settings.cache_key'));
    }
};
