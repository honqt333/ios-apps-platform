<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\AppResource;
use App\Repositories\Contracts\AppRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(protected AppRepositoryInterface $apps)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json([
                'success' => true,
                'data'    => [],
                'meta'    => ['total' => 0],
            ]);
        }

        $filters = [
            'category'  => $request->query('category'),
            'developer' => $request->query('developer'),
            'sort'      => $request->query('sort', 'newest'),
        ];

        $perPage = (int) min((int) $request->query('per_page', 20), 100);

        $paginator = $this->apps->search($term, $filters, $perPage);

        return response()->json([
            'success' => true,
            'data'    => AppResource::collection($paginator->items())->resolve(),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
            'query'   => $term,
        ]);
    }
}
