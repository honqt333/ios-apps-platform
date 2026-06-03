<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function find(int|string $id): ?Model
    {
        return $this->query()->find($id);
    }

    public function findOrFail(int|string $id): Model
    {
        return $this->query()->findOrFail($id);
    }

    public function findBy(string $field, mixed $value): ?Model
    {
        return $this->query()->where($field, $value)->first();
    }

    public function all(array $columns = ['*']): Collection
    {
        return $this->query()->get($columns);
    }

    public function paginate(int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        $query = $this->query();

        if (isset($filters['search']) && $filters['search']) {
            $this->applySearch($query, $filters['search']);
        }

        if (isset($filters['sort']) && method_exists($this, 'applySort')) {
            $this->applySort($query, $filters['sort']);
        }

        return $query->paginate($perPage);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(int|string $id, array $data): Model
    {
        $instance = $this->findOrFail($id);
        $instance->update($data);
        return $instance->fresh();
    }

    public function delete(int|string $id): bool
    {
        $instance = $this->findOrFail($id);
        return (bool) $instance->delete();
    }

    public function query(): Builder
    {
        return $this->model->newQuery();
    }

    protected function applySearch(Builder $query, string $term): Builder
    {
        return $query;
    }
}
