<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\AppResource;
use App\Repositories\Contracts\AppRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppController extends Controller
{
    public function __construct(protected AppRepositoryInterface $apps)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'q'         => $request->query('q'),
            'category'  => $request->query('category'),
            'developer' => $request->query('developer'),
            'sort'      => $request->query('sort', 'newest'),
        ];

        $perPage = (int) min((int) $request->query('per_page', 20), 100);

        $paginator = $this->apps->paginateWithFilters($filters, $perPage);

        return response()->json([
            'success' => true,
            'data'    => AppResource::collection($paginator->items())->resolve(),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    public function show(int|string $app): JsonResponse
    {
        $model = $this->apps->findByIdOrSlug($app);

        if (! $model || (! $model->is_active || $model->is_archived)) {
            return response()->json([
                'success' => false,
                'message' => __('errors.app_not_found'),
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => (new AppResource($model->load(['category', 'screenshots', 'files'])))->resolve(),
        ]);
    }

    public function featured(Request $request): JsonResponse
    {
        $limit = (int) min((int) $request->query('limit', 8), 30);
        $items = $this->apps->featured($limit);

        return response()->json([
            'success' => true,
            'data'    => AppResource::collection($items)->resolve(),
        ]);
    }

    public function mostDownloaded(Request $request): JsonResponse
    {
        $limit = (int) min((int) $request->query('limit', 10), 30);
        $items = $this->apps->mostDownloaded($limit);

        return response()->json([
            'success' => true,
            'data'    => AppResource::collection($items)->resolve(),
        ]);
    }

    public function recent(Request $request): JsonResponse
    {
        $limit = (int) min((int) $request->query('limit', 10), 30);
        $items = $this->apps->recent($limit);

        return response()->json([
            'success' => true,
            'data'    => AppResource::collection($items)->resolve(),
        ]);
    }
}
