<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Categories\StoreCategoryRequest;
use App\Http\Requests\Categories\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Services\Audit\Contracts\AuditServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryRepositoryInterface $categories,
        protected AuditServiceInterface $audit,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) min((int) $request->query('per_page', 50), 200);
        $paginator = \App\Models\Category::query()->orderBy('sort_order')->orderBy('name')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => CategoryResource::collection($paginator->items())->resolve(),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? $this->categories->ensureSlug($data['name']);

        $category = $this->categories->create($data);

        $this->audit->log('category.created', $category, $request->user());

        return response()->json([
            'success' => true,
            'message' => __('categories.created'),
            'data'    => (new CategoryResource($category))->resolve(),
        ], 201);
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

    public function update(UpdateCategoryRequest $request, string $category): JsonResponse
    {
        $model = Category::query()->where('slug', $category)->orWhere('id', $category)->first();
        if (! $model) {
            return response()->json([
                'success' => false,
                'message' => __('errors.category_not_found'),
            ], 404);
        }

        $data = $request->validated();
        if (! empty($data['name']) && empty($data['slug'])) {
            $data['slug'] = $this->categories->ensureSlug($data['name'], $model->id);
        }

        $model->fill($data);
        $model->save();

        $this->audit->log('category.updated', $model, $request->user());

        return response()->json([
            'success' => true,
            'message' => __('categories.updated'),
            'data'    => (new CategoryResource($model))->resolve(),
        ]);
    }

    public function destroy(Request $request, string $category): JsonResponse
    {
        $model = Category::query()->where('slug', $category)->orWhere('id', $category)->first();
        if (! $model) {
            return response()->json([
                'success' => false,
                'message' => __('errors.category_not_found'),
            ], 404);
        }

        $this->authorize('delete', $model);

        $this->audit->log('category.deleted', $model, $request->user());
        $model->delete();

        return response()->json([
            'success' => true,
            'message' => __('categories.deleted'),
        ]);
    }
}
