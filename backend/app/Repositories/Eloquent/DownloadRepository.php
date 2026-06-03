<?php

namespace App\Repositories\Eloquent;

use App\Models\Download;
use App\Repositories\Contracts\DownloadRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DownloadRepository extends BaseRepository implements DownloadRepositoryInterface
{
    public function __construct(Download $model)
    {
        parent::__construct($model);
    }

    public function record(array $data): Download
    {
        return $this->model->create($data);
    }

    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->query()->with(['app', 'user']);

        if (! empty($filters['app_id'])) {
            $query->where('app_id', $filters['app_id']);
        }

        if (! empty($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    public function totalCount(): int
    {
        return $this->query()->count();
    }

    public function countForApp(int $appId): int
    {
        return $this->query()->where('app_id', $appId)->count();
    }
}
