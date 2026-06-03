<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Auth\Contracts\AuthServiceInterface;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService implements AuthServiceInterface
{
    public function __construct(protected UserRepositoryInterface $users)
    {
    }

    public function register(array $data): User
    {
        $user = $this->users->create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'username' => $data['username'] ?? null,
            'password' => $data['password'],
            'phone'    => $data['phone'] ?? null,
            'locale'   => $data['locale'] ?? app()->getLocale(),
            'is_active' => true,
        ]);

        // Default role for newly registered users
        if (method_exists($user, 'assignRole')) {
            $user->assignRole('editor');
        }

        return $user;
    }

    public function login(string $email, string $password, bool $remember = false): array
    {
        $credentials = ['email' => $email, 'password' => $password];

        if (! $token = auth('api')->attempt($credentials)) {
            throw new \Illuminate\Auth\AuthenticationException(__('auth.failed'));
        }

        $user = auth('api')->user();

        if (! $user->is_active) {
            auth('api')->logout();
            throw new \RuntimeException(__('auth.account_disabled'));
        }

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => request()?->ip(),
        ]);

        return [
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth('api')->factory()->getTTL() * 60,
            'user'         => $user->fresh(),
        ];
    }

    public function logout(): bool
    {
        return (bool) auth('api')->logout();
    }

    public function refresh(): string
    {
        return auth('api')->refresh();
    }

    public function me(): ?User
    {
        return auth('api')->user();
    }

    public function updateProfile(User $user, array $data): User
    {
        $user->fill(array_intersect_key($data, array_flip(['name', 'username', 'phone', 'avatar', 'locale'])));
        $user->save();
        return $user;
    }

    public function changePassword(User $user, string $current, string $new): bool
    {
        if (! Hash::check($current, $user->password)) {
            throw new \RuntimeException(__('auth.password_mismatch'));
        }
        $user->password = $new;
        $user->save();
        return true;
    }
}
