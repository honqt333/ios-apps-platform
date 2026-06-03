<?php

use App\Http\Controllers\Api\Admin\ActivityLogController;
use App\Http\Controllers\Api\Admin\AppController as AdminAppController;
use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\UploadController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Public\AppController as PublicAppController;
use App\Http\Controllers\Api\Public\CategoryController as PublicCategoryController;
use App\Http\Controllers\Api\Public\DownloadController;
use App\Http\Controllers\Api\Public\SearchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// =====================================================================
// Public
// =====================================================================

Route::prefix('v1')->group(function () {

    Route::prefix('apps')->group(function () {
        Route::get('/',                [PublicAppController::class, 'index'])->name('api.apps.index');
        Route::get('/featured',        [PublicAppController::class, 'featured'])->name('api.apps.featured');
        Route::get('/most-downloaded', [PublicAppController::class, 'mostDownloaded'])->name('api.apps.most-downloaded');
        Route::get('/recent',          [PublicAppController::class, 'recent'])->name('api.apps.recent');
        Route::get('/{app}',           [PublicAppController::class, 'show'])->name('api.apps.show');
        Route::post('/{app}/track',    [DownloadController::class, 'track'])->name('api.apps.track');
        Route::get('/{app}/download',  [DownloadController::class, 'download'])->name('api.apps.download');
    });

    Route::prefix('categories')->group(function () {
        Route::get('/',        [PublicCategoryController::class, 'index'])->name('api.categories.index');
        Route::get('/tree',    [PublicCategoryController::class, 'tree'])->name('api.categories.tree');
        Route::get('/{category}', [PublicCategoryController::class, 'show'])->name('api.categories.show');
    });

    Route::get('/search', SearchController::class)->name('api.search');

    // =================================================================
    // Auth (Public)
    // =================================================================
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->name('api.auth.register');
        Route::post('/login',    [AuthController::class, 'login'])->name('api.auth.login');
    });
});

// =====================================================================
// Authenticated
// =====================================================================

Route::prefix('v1/auth')->middleware('jwt.auth')->group(function () {
    Route::post('/logout',            [AuthController::class, 'logout'])->name('api.auth.logout');
    Route::post('/refresh',           [AuthController::class, 'refresh'])->name('api.auth.refresh');
    Route::get('/me',                 [AuthController::class, 'me'])->name('api.auth.me');
    Route::patch('/me',               [AuthController::class, 'updateProfile'])->name('api.auth.update-profile');
    Route::post('/change-password',   [AuthController::class, 'changePassword'])->name('api.auth.change-password');
});

// =====================================================================
// Admin
// =====================================================================

Route::prefix('v1/admin')
    ->middleware(['jwt.auth', 'admin', 'audit'])
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'stats'])->name('api.admin.dashboard');

        // Apps
        Route::prefix('apps')->group(function () {
            Route::get('/',          [AdminAppController::class, 'index'])->name('api.admin.apps.index');
            Route::post('/',         [AdminAppController::class, 'store'])->name('api.admin.apps.store');
            Route::get('/{app}',     [AdminAppController::class, 'show'])->name('api.admin.apps.show');
            Route::put('/{app}',     [AdminAppController::class, 'update'])->name('api.admin.apps.update');
            Route::patch('/{app}',   [AdminAppController::class, 'update'])->name('api.admin.apps.patch');
            Route::delete('/{app}',  [AdminAppController::class, 'destroy'])->name('api.admin.apps.destroy');
            Route::post('/{app}/archive',      [AdminAppController::class, 'archive'])->name('api.admin.apps.archive');
            Route::post('/{app}/toggle-active', [AdminAppController::class, 'toggleActive'])->name('api.admin.apps.toggle-active');
        });

        // Uploads
        Route::prefix('upload')->group(function () {
            Route::post('/ipa',         [UploadController::class, 'ipa'])->name('api.admin.upload.ipa');
            Route::post('/icon',        [UploadController::class, 'icon'])->name('api.admin.upload.icon');
            Route::post('/screenshots', [UploadController::class, 'screenshots'])->name('api.admin.upload.screenshots');
        });

        // Categories
        Route::prefix('categories')->group(function () {
            Route::get('/',             [AdminCategoryController::class, 'index'])->name('api.admin.categories.index');
            Route::post('/',            [AdminCategoryController::class, 'store'])->name('api.admin.categories.store');
            Route::get('/{category}',    [AdminCategoryController::class, 'show'])->name('api.admin.categories.show');
            Route::put('/{category}',    [AdminCategoryController::class, 'update'])->name('api.admin.categories.update');
            Route::patch('/{category}',  [AdminCategoryController::class, 'update'])->name('api.admin.categories.patch');
            Route::delete('/{category}', [AdminCategoryController::class, 'destroy'])->name('api.admin.categories.destroy');
        });

        // Users
        Route::prefix('users')->group(function () {
            Route::get('/',          [AdminUserController::class, 'index'])->name('api.admin.users.index');
            Route::post('/',         [AdminUserController::class, 'store'])->name('api.admin.users.store');
            Route::get('/{user}',    [AdminUserController::class, 'show'])->name('api.admin.users.show');
            Route::put('/{user}',    [AdminUserController::class, 'update'])->name('api.admin.users.update');
            Route::patch('/{user}',  [AdminUserController::class, 'update'])->name('api.admin.users.patch');
            Route::delete('/{user}', [AdminUserController::class, 'destroy'])->name('api.admin.users.destroy');
        });

        // Activity logs
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])
            ->middleware('permission:audit.view')
            ->name('api.admin.activity-logs.index');
    });

// =====================================================================
// Health
// =====================================================================

Route::get('/health', fn () => response()->json([
    'status'    => 'ok',
    'timestamp' => now()->toIso8601String(),
    'service'   => 'ios-platform-api',
]))->name('api.health');
