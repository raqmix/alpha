<?php

namespace App\Classes;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Single source of truth for the Google reCAPTCHA integration.
 *
 * Keys are read from the admin panel (Site Settings > General > Google
 * Recaptcha) and fall back to Google's universal test keys whenever a field is
 * left blank, so every webform on the site stays functional either way.
 */
class Recaptcha
{
    public const SITE_KEY_SETTING = 'general.google_recaptcha_site_key';

    public const SECRET_KEY_SETTING = 'general.google_recaptcha_secret';

    public const ENABLED_SETTING = 'general.google_recaptcha_enabled';

    public static function siteKey(): string
    {
        return static::settingValue(static::SITE_KEY_SETTING)
            ?? config('recaptcha.universal.site_key');
    }

    public static function secretKey(): string
    {
        return static::settingValue(static::SECRET_KEY_SETTING)
            ?? config('recaptcha.universal.secret_key');
    }

    /**
     * Enabled unless the environment opts out or an admin explicitly turns it
     * off. A setting that was never saved counts as enabled.
     */
    public static function enabled(): bool
    {
        if (! config('recaptcha.enabled')) {
            return false;
        }

        $toggle = setting(static::ENABLED_SETTING);

        return is_null($toggle) ? true : static::toBool($toggle);
    }

    /**
     * True while either key is still Google's test pair, which means the widget
     * passes everything and protects nothing.
     */
    public static function usingUniversalKeys(): bool
    {
        return static::siteKey() === config('recaptcha.universal.site_key')
            || static::secretKey() === config('recaptcha.universal.secret_key');
    }

    /**
     * Ask Google whether the token the browser produced is genuine. Tokens are
     * single use and expire after two minutes, so only call this once per token.
     */
    public static function verify(?string $token, ?string $ip = null): bool
    {
        if (blank($token)) {
            return false;
        }

        try {
            $response = Http::timeout((int) config('recaptcha.timeout', 10))
                ->asForm()
                ->post(config('recaptcha.verify_url'), array_filter([
                    'secret' => static::secretKey(),
                    'response' => $token,
                    'remoteip' => $ip,
                ]));
        } catch (\Throwable $exception) {
            // Never let an outage on Google's side throw a 500 at a visitor who
            // is only trying to send us a message.
            Log::warning('reCAPTCHA verification request failed.', [
                'exception' => $exception->getMessage(),
            ]);

            return false;
        }

        if (! $response->successful()) {
            Log::warning('reCAPTCHA verification returned an error response.', [
                'status' => $response->status(),
            ]);

            return false;
        }

        if (! $response->json('success', false)) {
            Log::info('reCAPTCHA verification rejected a token.', [
                'errors' => $response->json('error-codes', []),
            ]);

            return false;
        }

        return true;
    }

    /**
     * A trimmed non-empty setting, or null when it is unset/blank so the caller
     * can fall back to the universal keys.
     */
    protected static function settingValue(string $key): ?string
    {
        $value = setting($key);

        if (! is_string($value)) {
            return null;
        }

        // Settings are stored json encoded and one of the two reader paths in
        // this app hands back the raw column, so a value can arrive still
        // wrapped in quotes.
        $value = trim($value);
        $value = trim($value, '"');
        $value = trim($value);

        return $value === '' ? null : $value;
    }

    /**
     * Public wrapper over the boolean cast, for the settings form to reuse.
     */
    public static function settingIsTruthy(mixed $value): bool
    {
        return static::toBool($value);
    }

    /**
     * Cast a stored setting to a boolean, tolerating the raw json form.
     * Anything unrecognisable falls back to enabled, which is the safe
     * direction for a spam check.
     */
    protected static function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (bool) $value;
        }

        if (is_string($value)) {
            $value = trim(trim(trim($value), '"'));

            if ($value === '') {
                return false;
            }

            return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;
        }

        return (bool) $value;
    }
}
