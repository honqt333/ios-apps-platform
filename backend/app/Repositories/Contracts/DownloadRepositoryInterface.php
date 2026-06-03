<?php

namespace App\Repositories\Contracts;

use App\Models\Download;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface DownloadRepositoryInterface
{
    public function record(array $data): Download;

    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator;

    public function totalCount(): int;

    public function countForApp(int $appId): int;
}
