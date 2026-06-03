<?php

namespace App\Repositories\Contracts;

use App\Models\App;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface AppRepositoryInterface
{
    public function paginateWithFilters(array $filters = [], int $perPage = 20): LengthAwarePaginator;

    public function listActive(array $filters = []): Collection;

    public function findByIdOrSlug(int|string $id): ?App;

    public function incrementDownloads(int $id): int;

    public function mostDownloaded(int $limit = 10): Collection;

    public function recent(int $limit = 10): Collection;

    public function featured(int $limit = 8): Collection;

    public function search(string $term, array $filters = []): LengthAwarePaginator;
}
