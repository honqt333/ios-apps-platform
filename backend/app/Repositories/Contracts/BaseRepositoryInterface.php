<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    public function find(int|string $id): ?Model;

    public function findOrFail(int|string $id): Model;

    public function findBy(string $field, mixed $value): ?Model;

    public function all(array $columns = ['*']): Collection;

    public function paginate(int $perPage = 20, array $filters = []): LengthAwarePaginator;

    public function create(array $data): Model;

    public function update(int|string $id, array $data): Model;

    public function delete(int|string $id): bool;

    public function query(): Builder;
}
