<?php

namespace App\Repositories\Contracts;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface
{
    public function tree(): Collection;

    public function active(): Collection;

    public function findBySlug(string $slug): ?Category;

    public function ensureSlug(string $name, ?int $ignoreId = null): string;
}
