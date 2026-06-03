<?php

namespace App\Services\Audit\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

interface AuditServiceInterface
{
    public function log(string $description, ?Model $subject = null, ?User $causer = null, array $properties = []): void;

    public function paginate(array $filters = [], int $perPage = 20);
}
