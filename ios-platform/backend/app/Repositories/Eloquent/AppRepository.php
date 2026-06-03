<?php

namespace App\Repositories\Eloquent;

use App\Models\App;
use App\Repositories\Contracts\AppRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class AppRepository extends BaseRepository implements AppRepositoryInterface
{
    public function __construct(App $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->buildQuery($filters);

        $sort = $filters['sort'] ?? 'newest';
        $query = $this->applySort($query, $sort);

        return $query->paginate($perPage)->withQueryString();
    }

    public function listActive(array $filters = []): Collection
    {
        $query = $this->buildQuery($filters);
        $query = $this->applySort($query, $filters['sort'] ?? 'newest');
        return $query->get();
    }

    public function findByIdOrSlug(int|string $id): ?App
    {
        return $this->query()
            ->with(['category', 'screenshots', 'files'])
            ->where(is_numeric($id) ? 'id' : 'slug', $id)
            ->first();
    }

    public function incrementDownloads(int $id): int
    {
        $app = $this->findOrFail($id);
        $app->increment('downloads_count');
        return $app->downloads_count;
    }

    public function mostDownloaded(int $limit = 10): Collection
    {
        return $this->query()
            ->active()
            ->orderByDesc('downloads_count')
            ->limit($limit)
            ->get();
    }

    public function recent(int $limit = 10): Collection
    {
        return $this->query()
            ->active()
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function featured(int $limit = 8): Collection
    {
        return $this->query()
            ->active()
            ->featured()
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get();
    }

    public function search(string $term, array $filters = []): LengthAwarePaginator
    {
        $filters['q'] = $term;
        $query = $this->buildQuery($filters);
        $query = $this->applySort($query, $filters['sort'] ?? 'newest');
        return $query->paginate($filters['per_page'] ?? 20)->withQueryString();
    }

    // -----------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------

    protected function buildQuery(array $filters): Builder
    {
        $query = $this->query()->with(['category', 'screenshots']);

        // Public listings should not see archived/inactive
        if (! ($filters['admin'] ?? false)) {
            $query->active();
        }

        if (! empty($filters['q'])) {
            $term = $filters['q'];
            $query->search($term);
        }

        if (! empty($filters['developer'])) {
            $query->ofDeveloper($filters['developer']);
        }

        if (! empty($filters['category'])) {
            $query->ofCategory($filters['category']);
        }

        if (isset($filters['is_archived'])) {
            $query->where('is_archived', (bool) $filters['is_archived']);
        }

        if (isset($filters['is_featured'])) {
            $query->where('is_featured', (bool) $filters['is_featured']);
        }

        return $query;
    }

    protected function applySort(Builder $query, string $sort): Builder
    {
        return $query->sortBy($sort);
    }

    protected function applySearch(Builder $query, string $term): Builder
    {
        return $query->search($term);
    }
}
