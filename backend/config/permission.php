<?php

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

return [
    'storage' => [
        'default' => 'database',

        'drivers' => [
            'database' => [
                'connection' => env('DB_CONNECTION', 'mysql'),
                'table'      => 'permissions',
            ],
        ],
    ],

    'models' => [
        'permission' => Spatie\Permission\Models\Permission::class,
        'role'       => Spatie\Permission\Models\Role::class,
    ],

    'column_names' => [
        'role_pivot_key'       => null,
        'permission_pivot_key' => null,
        'model_morph_key'      => 'model_id',
        'team_foreign_key'     => 'team_id',
    ],

    'register_permission_check_method' => true,

    'teams' => false,

    'display_permission_in_exception' => false,

    'display_role_in_exception' => false,

    'enable_wildcard_permission' => false,

    'cache' => [
        'expiration_time' => \DateInterval::createFromDateString('24 hours'),
        'key'             => 'spatie.permission.cache',
        'store'           => 'default',
    ],

    'roles' => [
        Role::SUPER_ADMIN => 'super-admin',
        Role::ADMIN       => 'admin',
        'moderator'       => 'moderator',
        'editor'          => 'editor',
    ],

    'permissions' => [
        'app.view'             => 'View apps',
        'app.create'           => 'Create apps',
        'app.update'           => 'Update apps',
        'app.delete'           => 'Delete apps',
        'app.archive'          => 'Archive apps',
        'app.publish'          => 'Publish / activate apps',
        'app.upload'           => 'Upload IPA / screenshots',

        'category.view'        => 'View categories',
        'category.manage'      => 'Manage categories',

        'user.view'            => 'View users',
        'user.create'          => 'Create users',
        'user.update'          => 'Update users',
        'user.delete'          => 'Delete users',
        'user.assign-role'     => 'Assign roles',

        'role.view'            => 'View roles',
        'role.manage'          => 'Manage roles',

        'audit.view'           => 'View activity logs',

        'settings.manage'      => 'Manage platform settings',
    ],
];
