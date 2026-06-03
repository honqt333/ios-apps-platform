<?php

namespace App\Services\Storage;

use App\Services\Storage\Contracts\StorageServiceInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StorageService implements StorageServiceInterface
{
    public function __construct(protected ?string $defaultDisk = null)
    {
        $this->defaultDisk = $defaultDisk ?? config('platform.storage.default_disk', 'local');
    }

    public function store(UploadedFile|string $file, string $directory, ?string $name = null, ?string $disk = null): array
    {
        $disk = $disk ?: $this->defaultDisk;
        $directory = trim($directory, '/');
        $name = $name ?: $this->generateName($file);

        $path = $file instanceof UploadedFile
            ? $file->storeAs($directory, $name, $disk)
            : Storage::disk($disk)->put($directory . '/' . $name, $file);

        if (! $path) {
            throw new \RuntimeException('Failed to store file');
        }

        $size = $this->size($path, $disk);

        return [
            'path' => $path,
            'disk' => $disk,
            'size' => $size,
            'url'  => $this->url($path, $disk),
        ];
    }

    public function delete(string $path, string $disk): bool
    {
        if (! $this->exists($path, $disk)) {
            return false;
        }
        return Storage::disk($disk)->delete($path);
    }

    public function exists(string $path, string $disk): bool
    {
        return Storage::disk($disk)->exists($path);
    }

    public function url(string $path, string $disk): ?string
    {
        try {
            return Storage::disk($disk)->url($path);
        } catch (\Throwable) {
            return null;
        }
    }

    public function size(string $path, string $disk): int
    {
        try {
            return (int) Storage::disk($disk)->size($path);
        } catch (\Throwable) {
            return 0;
        }
    }

    public function checksum(string $path, string $disk): ?string
    {
        try {
            return hash_file('sha256', Storage::disk($disk)->path($path));
        } catch (\Throwable) {
            return null;
        }
    }

    public function disk(): string
    {
        return $this->defaultDisk;
    }

    protected function generateName(UploadedFile|string $file): string
    {
        if ($file instanceof UploadedFile) {
            $original = $file->getClientOriginalName();
        } else {
            return Str::random(40);
        }
        return Str::slug(pathinfo($original, PATHINFO_FILENAME)) . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
    }
}
