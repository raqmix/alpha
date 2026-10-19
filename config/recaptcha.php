<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Master switch
    |--------------------------------------------------------------------------
    |
    | Hard kill switch for the whole integration. The day to day on/off toggle
    | lives in the admin panel under Site Settings > General > Google Recaptcha,
    | this one only exists so a single environment can opt out entirely.
    |
    */

    'enabled' => (bool) env('RECAPTCHA_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Universal (test) keys
    |--------------------------------------------------------------------------
    |
    | Google's documented "universal" key pair for automated testing. The widget
    | always renders, always returns a token and every verification request
    | passes, so the webforms keep working out of the box before anyone has
    | saved a real key pair in Site Settings.
    |
    | They give no actual bot protection and the widget shows a
    | "for testing purposes only" warning, so real keys should be entered in
    | Site Settings for production.
    |
    | @see https://developers.google.com/recaptcha/docs/faq
    |
    */

    'universal' => [
        'site_key' => '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI',
        'secret_key' => '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe',
    ],

    /*
    |--------------------------------------------------------------------------
    | Endpoints
    |--------------------------------------------------------------------------
    */

    'script_url' => 'https://www.google.com/recaptcha/api.js',

    'verify_url' => 'https://www.google.com/recaptcha/api/siteverify',

    'timeout' => 10,

];
