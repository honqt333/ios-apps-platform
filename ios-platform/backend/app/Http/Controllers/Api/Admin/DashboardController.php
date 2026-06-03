<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\Category;
use App\Models\Download;
use App\Models\User;
use App\Repositories\Contracts\DownloadRepositoryInterface;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(protected DownloadRepositoryInterface $downloads)
    {
    }

    public function stats(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => [
                'totals' => [
                    'apps'        => App::count(),
                    'categories'  => Category::count(),
                    'users'       => User::count(),
                    'downloads'   => $this->downloads->totalCount(),
                    'active_apps' => App::where('is_active', true)->where('is_archived', false)->count(),
                    'archived_apps' => App::where('is_archived', true)->count(),
                ],
                'recent_apps' => App::with('category')
                    ->orderByDesc('created_at')
                    ->limit(8)
                    ->get(['id', 'name', 'slug', 'version', 'icon_path', 'category_id', 'created_at']),
                'top_apps' => App::orderByDesc('downloads_count')
                    ->limit(8)
                    ->get(['id', 'name', 'slug', 'version', 'icon_path', 'downloads_count']),
                'recent_downloads' => Download::with(['app:id,name,slug,icon_path', 'user:id,name,email'])
                    ->orderByDesc('created_at')
                    ->limit(10)
                    ->get(),
            ],
        ]);
    }
}
