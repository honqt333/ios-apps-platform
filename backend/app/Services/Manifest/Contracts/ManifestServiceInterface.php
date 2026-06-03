<?php

namespace App\Services\Manifest\Contracts;

use App\Models\App;
use App\Models\AppFile;

interface ManifestServiceInterface
{
    /**
     * Generate a manifest plist for an app and write it to disk.
     * Returns the manifest's public URL path (relative to APP_URL).
     */
    public function generate(App $app, AppFile $file, ?string $disk = null): string;

    public function buildXml(array $payload): string;

    public function buildInstallUrl(string $manifestUrl): string;
}
