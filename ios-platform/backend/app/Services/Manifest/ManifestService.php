<?php

namespace App\Services\Manifest;

use App\Models\App;
use App\Models\AppFile;
use App\Services\Manifest\Contracts\ManifestServiceInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ManifestService implements ManifestServiceInterface
{
    public function generate(App $app, AppFile $file, ?string $disk = null): string
    {
        $disk = $disk ?: config('platform.manifest.public_disk', 'public');
        $directory = trim(config('platform.manifest.storage_path', 'manifests'), '/');

        $ipaUrl = $this->buildIpaUrl($file);

        $payload = [
            'ipa_url'           => $ipaUrl,
            'bundle_identifier' => $app->bundle_id,
            'bundle_version'    => $file->version ?? $app->version,
            'title'             => $app->name,
            'subtitle'          => $app->developer,
            'icon_url'          => $app->icon_url,
        ];

        $xml = $this->buildXml($payload);

        $filename = $app->slug . '_' . ($file->version ?: $app->version) . '_' . Str::random(6) . '.plist';
        $path = $directory . '/' . $filename;

        Storage::disk($disk)->put($path, $xml);

        return $path;
    }

    public function buildXml(array $payload): string
    {
        $ipaUrl           = $payload['ipa_url'] ?? '';
        $bundleIdentifier = $payload['bundle_identifier'] ?? '';
        $bundleVersion    = $payload['bundle_version'] ?? '1.0.0';
        $title            = $payload['title'] ?? 'App';
        $subtitle         = $payload['subtitle'] ?? '';
        $iconUrl          = $payload['icon_url'] ?? '';

        $subtitleXml = $subtitle ? "<key>subtitle</key><string>{$this->escape($subtitle)}</string>" : '';
        $iconXml     = $iconUrl ? "<key>icon</key><string>{$this->escape($iconUrl)}</string>" : '';

        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
    <key>items</key>
    <array>
        <dict>
            <key>assets</key>
            <array>
                <dict>
                    <key>kind</key>
                    <string>software-package</string>
                    <key>url</key>
                    <string>{$this->escape($ipaUrl)}</string>
                </dict>
                {$iconXml}
            </array>
            <key>metadata</key>
            <dict>
                <key>bundle-identifier</key>
                <string>{$this->escape($bundleIdentifier)}</string>
                <key>bundle-version</key>
                <string>{$this->escape($bundleVersion)}</string>
                <key>kind</key>
                <string>software</string>
                <key>title</key>
                <string>{$this->escape($title)}</string>
                {$subtitleXml}
            </dict>
        </dict>
    </array>
</dict>
</plist>
XML;

        return $xml;
    }

    public function buildInstallUrl(string $manifestUrl): string
    {
        return 'itms-services://?action=download-manifest&url=' . urlencode($manifestUrl);
    }

    protected function buildIpaUrl(AppFile $file): string
    {
        $disk = $file->disk ?: config('platform.storage.default_disk', 'local');
        $url  = \Storage::disk($disk)->url($file->path);
        return $url;
    }

    protected function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
