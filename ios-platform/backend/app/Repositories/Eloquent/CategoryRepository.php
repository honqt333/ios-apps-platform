<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    public function tree(): Collection
    {
        return $this->query()
            ->whereNull('parent_id')
            ->with('children')
            ->ordered()
            ->get();
    }

    public function active(): Collection
    {
        return $this->query()->active()->ordered()->get();
    }

    public function findBySlug(string $slug): ?Category
    {
        return $this->query()->where('slug', $slug)->first();
    }

    public function ensureSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $i = 1;

        $query = $this->query()->where('slug', $slug);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        while ($query->exists()) {
            $slug = $original . '-' . (++$i);
            $query = $this->query()->where('slug', $slug);
            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }
        }

        return $slug;
    }
}
