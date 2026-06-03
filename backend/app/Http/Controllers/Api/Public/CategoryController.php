<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function __construct(protected CategoryRepositoryInterface $categories)
    {
    }

    public function index(): JsonResponse
    {
        $items = $this->categories->active();

        return response()->json([
            'success' => true,
            'data'    => CategoryResource::collection($items)->resolve(),
        ]);
    }

    public function tree(): JsonResponse
    {
        $items = $this->categories->tree();

        return response()->json([
            'success' => true,
            'data'    => CategoryResource::collection($items)->resolve(),
        ]);
    }

    public function show(string $category): JsonResponse
    {
        $model = $this->categories->findBySlug($category);

        if (! $model) {
            return response()->json([
                'success' => false,
                'message' => __('errors.category_not_found'),
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => (new CategoryResource($model))->resolve(),
        ]);
    }
}
