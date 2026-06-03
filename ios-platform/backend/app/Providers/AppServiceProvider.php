<?php

namespace App\Providers;

use App\Repositories\Contracts\AppRepositoryInterface;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\DownloadRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\AppRepository;
use App\Repositories\Eloquent\CategoryRepository;
use App\Repositories\Eloquent\DownloadRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Services\Auth\AuthService;
use App\Services\Auth\Contracts\AuthServiceInterface;
use App\Services\Audit\AuditService;
use App\Services\Audit\Contracts\AuditServiceInterface;
use App\Services\IpaParser\Contracts\IpaParserServiceInterface;
use App\Services\IpaParser\IpaParserService;
use App\Services\Manifest\Contracts\ManifestServiceInterface;
use App\Services\Manifest\ManifestService;
use App\Services\Storage\Contracts\StorageServiceInterface;
use App\Services\Storage\StorageService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repositories
        $this->app->bind(AppRepositoryInterface::class, AppRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(DownloadRepositoryInterface::class, DownloadRepository::class);

        // Services
        $this->app->bind(StorageServiceInterface::class, StorageService::class);
        $this->app->bind(IpaParserServiceInterface::class, IpaParserService::class);
        $this->app->bind(ManifestServiceInterface::class, ManifestService::class);
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(AuditServiceInterface::class, AuditService::class);
    }

    public function boot(): void
    {
        // Implicit route model binding for slug
        \Illuminate\Database\Eloquent\Model::shouldBeStrict(! app()->isProduction());
    }
}
