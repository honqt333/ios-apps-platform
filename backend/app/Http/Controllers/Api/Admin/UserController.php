<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Audit\Contracts\AuditServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct(
        protected UserRepositoryInterface $users,
        protected AuditServiceInterface $audit,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'search'    => $request->query('search'),
            'role'      => $request->query('role'),
            'is_active' => $request->query('is_active'),
        ];

        $perPage = (int) min((int) $request->query('per_page', 20), 100);
        $paginator = $this->users->paginate($filters, $perPage);

        return response()->json([
            'success' => true,
            'data'    => UserResource::collection($paginator->items())->resolve(),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = $this->users->create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'username'  => $data['username'] ?? null,
            'phone'     => $data['phone'] ?? null,
            'locale'    => $data['locale'] ?? 'en',
            'password'  => $data['password'],
            'is_active' => $data['is_active'] ?? true,
        ]);

        if (! empty($data['role'])) {
            $user->assignRole($data['role']);
        }

        $this->audit->log('user.created', $user, $request->user());

        return response()->json([
            'success' => true,
            'message' => __('users.created'),
            'data'    => (new UserResource($user->load('roles')))->resolve(),
        ], 201);
    }

    public function show(int $user): JsonResponse
    {
        $model = $this->users->findById($user);
        if (! $model) {
            return response()->json([
                'success' => false,
                'message' => __('errors.user_not_found'),
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => (new UserResource($model->load('roles')))->resolve(),
        ]);
    }

    public function update(UpdateUserRequest $request, int $user): JsonResponse
    {
        $model = $this->users->findById($user);
        if (! $model) {
            return response()->json([
                'success' => false,
                'message' => __('errors.user_not_found'),
            ], 404);
        }

        $data = $request->validated();

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $model->fill(array_intersect_key($data, array_flip(['name', 'email', 'username', 'phone', 'locale', 'is_active', 'password'])));
        $model->save();

        if (! empty($data['role'])) {
            $model->syncRoles([$data['role']]);
        }

        $this->audit->log('user.updated', $model, $request->user());

        return response()->json([
            'success' => true,
            'message' => __('users.updated'),
            'data'    => (new UserResource($model->fresh('roles')))->resolve(),
        ]);
    }

    public function destroy(Request $request, int $user): JsonResponse
    {
        $model = $this->users->findById($user);
        if (! $model) {
            return response()->json([
                'success' => false,
                'message' => __('errors.user_not_found'),
            ], 404);
        }

        $this->authorize('delete', $model);

        $this->audit->log('user.deleted', $model, $request->user());
        $model->delete();

        return response()->json([
            'success' => true,
            'message' => __('users.deleted'),
        ]);
    }
}
