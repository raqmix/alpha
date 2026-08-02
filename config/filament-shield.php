<?php

use BezhanSalleh\FilamentShield\Resources\Roles\RoleResource;
use Filament\Pages\Dashboard;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;

/*
|--------------------------------------------------------------------------
| Filament Shield v4
|--------------------------------------------------------------------------
|
| Rewritten for Shield v4, which restructured this file. Mapping from the
| previous v3 config:
|
|   auth_provider_model.fqcn        -> auth_provider_model (plain string)
|   permission_prefixes             -> permissions (Permission Builder)
|   generator.option                -> permissions.generate + policies.generate
|   generator.policy_directory      -> policies.path
|   exclude.pages/widgets/resources -> pages.exclude / widgets.exclude /
|                                      resources.exclude
|   register_role_policy.enabled    -> register_role_policy (bool)
|
| The navigation keys that used to live under shield_resource
| (should_register_navigation, navigation_sort, navigation_badge,
| navigation_group, is_globally_searchable) moved onto the plugin itself and
| are now set fluently in AdminPanelProvider.
|
| NOTE: v4 generates permission keys via the Permission Builder below
| ('ViewAny:Blog' by default) rather than v3's 'view_any_blog'. Existing
| permission rows and role assignments need to be regenerated/migrated on any
| environment that already has data.
|
*/

return [

    'shield_resource' => [
        'slug' => 'shield/roles',
        'show_model_path' => true,
        'cluster' => null,
        'tabs' => [
            'pages' => true,
            'widgets' => true,
            'resources' => true,
            'custom_permissions' => false,
        ],
    ],

    'tenant_model' => null,

    'auth_provider_model' => 'App\\Models\\User',

    'super_admin' => [
        'enabled' => true,
        'name' => 'super_admin',
        'define_via_gate' => false,
        'intercept_gate' => 'before', // after
    ],

    'panel_user' => [
        'enabled' => true,
        'name' => 'panel_user',
    ],

    'permissions' => [
        'separator' => ':',
        'case' => 'pascal',
        'generate' => true,
        'format_custom_permission_keys' => true,
    ],

    'policies' => [
        'path' => app_path('Policies'),
        'merge' => true,
        'generate' => true,
        'methods' => [
            'viewAny', 'view', 'create', 'update', 'delete', 'deleteAny', 'restore',
            'forceDelete', 'forceDeleteAny', 'restoreAny', 'replicate', 'reorder',
        ],
        'single_parameter_methods' => [
            'viewAny',
            'create',
            'deleteAny',
            'forceDeleteAny',
            'restoreAny',
            'reorder',
        ],
    ],

    'localization' => [
        'enabled' => false,
        'key' => 'filament-shield::filament-shield.resource_permission_prefixes_labels',
    ],

    'resources' => [
        'subject' => 'model',
        'manage' => [
            RoleResource::class => [
                'viewAny',
                'view',
                'create',
                'update',
                'delete',
            ],
        ],
        'exclude' => [
            //
        ],
    ],

    'pages' => [
        'subject' => 'class',
        'prefix' => 'view',
        'exclude' => [
            Dashboard::class,
        ],
    ],

    'widgets' => [
        'subject' => 'class',
        'prefix' => 'view',
        'exclude' => [
            AccountWidget::class,
            FilamentInfoWidget::class,
        ],
    ],

    'custom_permissions' => [],

    /*
     * Carried over from the v3 config, which had all three enabled.
     */
    'discovery' => [
        'discover_all_resources' => true,
        'discover_all_widgets' => true,
        'discover_all_pages' => true,
    ],

    'register_role_policy' => true,

];
