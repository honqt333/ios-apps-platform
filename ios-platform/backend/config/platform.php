<?php

return [
    'app' => [
        'name'     => env('APP_NAME', 'iOS Apps Platform'),
        'env'      => env('APP_ENV', 'production'),
        'debug'    => (bool) env('APP_DEBUG', false),
        'url'      => env('APP_URL', 'http://localhost'),
        'timezone' => env('APP_TIMEZONE', 'UTC'),
        'locale'   => env('APP_LOCALE', 'en'),
        'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
        'faker_locale'    => env('APP_FAKER_LOCALE', 'en_US'),
        'cipher'   => 'AES-256-CBC',
        'key'      => env('APP_KEY'),
        'previous_keys' => [
            ...array_filter(explode(',', env('APP_PREVIOUS_KEYS', ''))),
        ],
        'maintenance' => [
            'driver' => 'file',
        ],
        'providers' => \Illuminate\Support\ServiceProvider::defaultProviders()->merge([
            App\Providers\AppServiceProvider::class,
        ])->toArray(),
    ],

    'platform' => [
        'storage' => [
            'default_disk'      => env('FILESYSTEM_DISK', 'local'),
            'public_disk'       => env('PUBLIC_DISK', 'public'),
            'path_prefix'       => env('STORAGE_PATH_PREFIX', 'apps'),
            'max_size_mb'       => (int) env('UPLOAD_MAX_SIZE_MB', 512),
            'mime_check'        => (bool) env('UPLOAD_IPA_MIME_CHECK', true),
            'extension_check'   => (bool) env('UPLOAD_IPA_EXT_CHECK', true),
            'allowed_apps_disk' => ['local', 's3', 'r2'],
        ],

        'ipa' => [
            'parse_on_upload'    => true,
            'extract_icon'       => true,
            'extract_metadata'   => true,
        ],

        'manifest' => [
            'base_url'        => env('MANIFEST_BASE_URL', env('APP_URL')),
            'url_path_prefix' => '/storage/manifests',
            'public_disk'     => 'public',
            'storage_path'    => 'manifests',
        ],

        'downloads' => [
            'token_ttl_hours'    => (int) env('IPA_DOWNLOAD_TOKEN_TTL_HOURS', 24),
            'signed_url_enabled' => true,
        ],

        'rate_limit' => [
            'public_per_minute'  => (int) env('RATE_LIMIT_PER_MINUTE', 60),
            'admin_per_minute'   => (int) env('RATE_LIMIT_ADMIN_PER_MINUTE', 120),
            'auth_per_minute'    => (int) env('RATE_LIMIT_AUTH_PER_MINUTE', 10),
        ],
    ],
];
