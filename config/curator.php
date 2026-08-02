<?php

declare(strict_types=1);

use App\Models\Media\Media;

/*
|--------------------------------------------------------------------------
| Curator v5
|--------------------------------------------------------------------------
|
| Rewritten for Curator v5, which restructured this file. Mapping from the
| previous v3 config:
|
|   disk                            -> default_disk
|   directory                       -> default_directory
|   visibility                      -> default_visibility
|   should_preserve_filenames       -> features.preserve_file_names
|   is_limited_to_directory         -> features.directory_restriction
|   is_tenant_aware / tenant_...    -> features.tenancy
|   tabs.display_curation           -> features.curations
|   resources                       -> resource (+ navigation subkey)
|   table.layout                    -> resource.default_layout
|   glide.*                         -> glide_token / url_provider
|
| Dropped in v5 (no longer read by the package): accepted_file_types,
| cloud_disks, curation_presets, image_* , max_size, min_size,
| should_check_exists, multi_select_key. Upload constraints are now set on
| the CuratorPicker field itself.
|
*/

return [
    'curation_formats' => [
        'jpg',
        'jpeg',
        'webp',
        'png',
        'avif',
        'mp4',
    ],

    'default_disk' => env('CURATOR_DEFAULT_DISK', env('FILAMENT_FILESYSTEM_DISK', 'public')),

    'default_directory' => 'media',

    'default_visibility' => 'public',

    'features' => [
        'curations' => true,
        'file_swap' => true,
        'directory_restriction' => false,
        'preserve_file_names' => false,
        'tenancy' => [
            'enabled' => false,
            'relationship_name' => null,
        ],
    ],

    /*
     * Signed-token secret for the Glide image server. Must be a non-empty
     * string in v5 -- GlideManager::getToken() is typed : string.
     */
    'glide_token' => env('CURATOR_GLIDE_TOKEN', env('APP_KEY', 'curator')),

    'model' => Media::class,

    'path_generator' => null,

    'resource' => [
        'label' => 'Media',
        'plural_label' => 'Media',
        'default_layout' => 'grid',
        'navigation' => [
            'group' => null,
            'icon' => 'heroicon-o-photo',
            'sort' => null,
            'should_register' => true,
            'should_show_badge' => false,
        ],
        'resource' => Awcodes\Curator\Resources\Media\MediaResource::class,
        'pages' => [
            'create' => Awcodes\Curator\Resources\Media\Pages\CreateMedia::class,
            'edit' => Awcodes\Curator\Resources\Media\Pages\EditMedia::class,
            'index' => Awcodes\Curator\Resources\Media\Pages\ListMedia::class,
        ],
        'schemas' => [
            'form' => Awcodes\Curator\Resources\Media\Schemas\MediaForm::class,
        ],
        'tables' => [
            'table' => Awcodes\Curator\Resources\Media\Tables\MediaTable::class,
        ],
    ],

    'url_provider' => Awcodes\Curator\Providers\GlideUrlProvider::class,
];
