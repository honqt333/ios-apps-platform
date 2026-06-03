<?php

namespace App\Services\Storage\Contracts;

use Illuminate\Http\UploadedFile;

interface StorageServiceInterface
{
    /**
     * Store a file (UploadedFile or raw content) on the configured disk.
     *
     * @return array{path: string, disk: string, size: int, url: ?string}
     */
    public function store(UploadedFile|string $file, string $directory, ?string $name = null, ?string $disk = null): array;

    public function delete(string $path, string $disk): bool;

    public function exists(string $path, string $disk): bool;

    public function url(string $path, string $disk): ?string;

    public function size(string $path, string $disk): int;

    public function checksum(string $path, string $disk): ?string;

    public function disk(): string;
}
