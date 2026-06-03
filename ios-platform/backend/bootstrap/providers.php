<?php

return [
    App\Providers\AppServiceProvider::class,
    Tymon\JWTAuth\Providers\LaravelServiceProvider::class,
    Spatie\Permission\PermissionServiceProvider::class,
    Spatie\Activitylog\ActivitylogServiceProvider::class,
    Spatie\MediaLibrary\MediaLibraryServiceProvider::class,
];
