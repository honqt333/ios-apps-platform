<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class App extends Model
{
    use HasFactory;
    use SoftDeletes;
    use LogsActivity;

    protected $table = 'apps';

    protected $fillable = [
        'name',
        'slug',
        'developer',
        'description',
        'long_description',
        'bundle_id',
        'version',
        'build_number',
        'minimum_ios_version',
        'file_size_bytes',
        'file_size_human',
        'category_id',
        'icon_path',
        'icon_url',
        'ipa_path',
        'ipa_disk',
        'manifest_path',
        'ipa_size_bytes',
        'install_token',
        'install_token_expires_at',
        'downloads_count',
        'is_active',
        'is_archived',
        'is_featured',
        'changelog',
        'changelog_history',
        'localized',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'file_size_bytes'         => 'integer',
        'ipa_size_bytes'          => 'integer',
        'downloads_count'         => 'integer',
        'is_active'               => 'boolean',
        'is_archived'             => 'boolean',
        'is_featured'             => 'boolean',
        'install_token_expires_at' => 'datetime',
        'changelog_history'       => 'array',
        'localized'               => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'version', 'is_active', 'is_archived', 'category_id', 'bundle_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('apps');
    }

    // -----------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function screenshots(): HasMany
    {
        return $this->hasMany(Screenshot::class)->orderBy('sort_order');
    }

    public function files(): HasMany
    {
        return $this->hasMany(AppFile::class)->orderByDesc('created_at');
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(Download::class);
    }

    public function currentFile()
    {
        return $this->hasOne(AppFile::class)->where('is_current', true);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // -----------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('is_archived', false);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('is_archived', true);
    }

    public function scopeOfCategory(Builder $query, int|string $category): Builder
    {
        if (is_numeric($category)) {
            return $query->where('category_id', (int) $category);
        }
        return $query->whereHas('category', fn ($q) => $q->where('slug', $category));
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        $like = '%' . $term . '%';

        return $query->where(function ($q) use ($like) {
            $q->where('name', 'like', $like)
              ->orWhere('developer', 'like', $like)
              ->orWhere('description', 'like', $like)
              ->orWhere('bundle_id', 'like', $like);
        });
    }

    public function scopeOfDeveloper(Builder $query, string $developer): Builder
    {
        return $query->where('developer', 'like', '%' . $developer . '%');
    }

    public function scopeSortBy(Builder $query, string $sort = 'newest'): Builder
    {
        return match ($sort) {
            'downloads' => $query->orderByDesc('downloads_count')->orderByDesc('updated_at'),
            'name'      => $query->orderBy('name'),
            'oldest'    => $query->orderBy('created_at'),
            default     => $query->orderByDesc('created_at')->orderByDesc('updated_at'),
        };
    }

    // -----------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getIconUrlAttribute(): ?string
    {
        if ($this->icon_url) {
            return $this->icon_url;
        }

        if ($this->icon_path) {
            return asset('storage/' . ltrim($this->icon_path, '/'));
        }

        return null;
    }

    public function getInstallUrlAttribute(): ?string
    {
        if (! $this->manifest_path) {
            return null;
        }

        $base = rtrim(config('platform.manifest.base_url'), '/');
        $path = ltrim($this->manifest_path, '/');
        $url  = $base . '/' . $path;

        return 'itms-services://?action=download-manifest&url=' . urlencode($url);
    }

    public function incrementDownloads(): void
    {
        $this->increment('downloads_count');
    }

    public function isInstallable(): bool
    {
        return $this->is_active
            && ! $this->is_archived
            && $this->manifest_path
            && $this->ipa_path;
    }
}
