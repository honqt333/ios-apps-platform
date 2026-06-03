<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\Contracts\AuthServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(protected AuthServiceInterface $auth)
    {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->auth->register($request->validated());
        $token = auth('api')->login($user);

        return response()->json([
            'success' => true,
            'message' => __('auth.registered'),
            'data'    => [
                'user'         => (new UserResource($user->load('roles')))->resolve(),
                'access_token' => $token,
                'token_type'   => 'bearer',
                'expires_in'   => auth('api')->factory()->getTTL() * 60,
            ],
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $payload = $this->auth->login(
            $request->string('email'),
            $request->string('password'),
            (bool) $request->boolean('remember')
        );

        return response()->json([
            'success' => true,
            'message' => __('auth.logged_in'),
            'data'    => [
                'user'         => (new UserResource($payload['user']->load('roles')))->resolve(),
                'access_token' => $payload['access_token'],
                'token_type'   => $payload['token_type'],
                'expires_in'   => $payload['expires_in'],
            ],
        ]);
    }

    public function logout(): JsonResponse
    {
        $this->auth->logout();
        return response()->json([
            'success' => true,
            'message' => __('auth.logged_out'),
        ]);
    }

    public function refresh(): JsonResponse
    {
        $token = $this->auth->refresh();
        return response()->json([
            'success' => true,
            'data'    => [
                'access_token' => $token,
                'token_type'   => 'bearer',
                'expires_in'   => auth('api')->factory()->getTTL() * 60,
            ],
        ]);
    }

    public function me(): JsonResponse
    {
        $user = $this->auth->me();
        if (! $user) {
            return response()->json(['success' => false, 'message' => __('auth.unauthenticated')], 401);
        }
        return response()->json([
            'success' => true,
            'data'    => (new UserResource($user->load('roles')))->resolve(),
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $this->auth->me();
        $data = $request->validate([
            'name'     => ['nullable', 'string', 'max:120'],
            'username' => ['nullable', 'string', 'max:60'],
            'phone'    => ['nullable', 'string', 'max:32'],
            'avatar'   => ['nullable', 'image', 'max:2048'],
            'locale'   => ['nullable', 'in:en,ar'],
        ]);

        $user = $this->auth->updateProfile($user, $data);

        return response()->json([
            'success' => true,
            'message' => __('auth.profile_updated'),
            'data'    => (new UserResource($user->fresh('roles')))->resolve(),
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $this->auth->me();
        $this->auth->changePassword($user, $data['current_password'], $data['password']);

        return response()->json([
            'success' => true,
            'message' => __('auth.password_changed'),
        ]);
    }
}
