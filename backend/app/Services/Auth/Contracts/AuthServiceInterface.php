<?php

namespace App\Services\Auth\Contracts;

use App\Models\User;

interface AuthServiceInterface
{
    public function register(array $data): User;

    public function login(string $email, string $password, bool $remember = false): array;

    public function logout(): bool;

    public function refresh(): string;

    public function me(): ?User;

    public function updateProfile(User $user, array $data): User;

    public function changePassword(User $user, string $current, string $new): bool;
}
