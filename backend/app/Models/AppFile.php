<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'app_id',
        'version',
        'build_number',
        'disk',
        'path',
        'manifest_path',
        'size_bytes',
        'checksum_sha256',
        'metadata',
        'is_current',
    ];

    protected $casts = [
        'metadata'    => 'array',
        'is_current'  => 'boolean',
        'size_bytes'  => 'integer',
    ];

    public function app(): BelongsTo
    {
        return $this->belongsTo(App::class);
    }

    public function getUrlAttribute(): ?string
    {
        if (! $this->path) {
            return null;
        }

        return \Storage::disk($this->disk)->url($this->path);
    }

    public function getManifestUrlAttribute(): ?string
    {
        if (! $this->manifest_path) {
            return null;
        }

        $base = rtrim(config('platform.manifest.base_url'), '/');

        return $base . '/' . ltrim($this->manifest_path, '/');
    }

    public function getSizeHumanAttribute(): string
    {
        $bytes = (int) $this->size_bytes;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
