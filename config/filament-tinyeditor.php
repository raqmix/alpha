<?php

/*
|--------------------------------------------------------------------------
| TinyMCE editor (amidesfahani/filament-tinyeditor)
|--------------------------------------------------------------------------
|
| Migrated from config/filament-forms-tinyeditor.php when
| mohamedsabil83/filament-forms-tinyeditor was dropped (it has no Filament v5
| release). The replacement package ships TinyMCE 8, so plugin and toolbar
| names below were updated from their TinyMCE 5 spellings:
|
|   fontselect -> fontfamily, fontsizeselect -> fontsize, formatselect -> blocks
|
| Plugins removed by TinyMCE 6/7/8 have been dropped from the lists:
|   print, paste, imagetools, textpattern, noneditable, template, toc, hr
|
| The 'with-bootstrap' profile also used a licensed third-party `bootstrap`
| plugin loaded from the self-hosted TinyMCE 5.0.16 build in public/js/tinymce
| (via base_url + bootstrapConfig). That plugin is not compatible with
| TinyMCE 8 and BOOTSTRAP_EDITOR_KEY was never set in .env, so it is not
| loaded here. See the upgrade notes if that toolbar button is still needed.
|
*/

return [
    'version' => [
        'tiny' => '8.0.2',
        'language' => [
            'version' => '25.8.4',
            'package' => 'langs8',
        ],
        'licence_key' => env('TINY_LICENSE_KEY', 'no-api-key'),
    ],

    'provider' => 'cloud', // cloud|vendor

    'darkMode' => 'auto',

    'skins' => [
        'ui' => 'oxide',
        'content' => 'default',
    ],

    'profiles' => [

        'default' => [
            'plugins' => 'advlist autoresize codesample directionality emoticons fullscreen image link lists media table wordcount',
            'toolbar' => 'undo redo removeformat | blocks fontsize | bold italic | rtl ltr | alignjustify alignright aligncenter alignleft | numlist bullist | forecolor backcolor | blockquote table | image link media codesample emoticons | wordcount fullscreen',
            'upload_directory' => null,
        ],

        'simple' => [
            'plugins' => 'autoresize directionality emoticons link wordcount',
            'toolbar' => 'removeformat | bold italic | rtl ltr | link emoticons',
            'upload_directory' => null,
        ],

        'with-bootstrap' => [
            'plugins' => 'preview importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media codesample table charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount help quickbars emoticons accordion',
            'toolbar' => 'undo redo | bold italic underline strikethrough | fontfamily fontsize blocks | alignleft aligncenter alignright alignjustify | outdent indent | numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen preview save | image media link anchor codesample | ltr rtl',
            'upload_directory' => null,
            'custom_configs' => [
                'height' => 600,
            ],
        ],

    ],

    'languages' => [],

    'extra' => [
        'toolbar' => [],
    ],
];
