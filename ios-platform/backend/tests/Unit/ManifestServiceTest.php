<?php

namespace Tests\Unit;

use App\Services\Manifest\ManifestService;
use Tests\TestCase;

class ManifestServiceTest extends TestCase
{
    public function test_builds_valid_manifest_xml(): void
    {
        $service = new ManifestService();

        $xml = $service->buildXml([
            'ipa_url'           => 'https://example.com/app.ipa',
            'bundle_identifier' => 'com.example.app',
            'bundle_version'    => '1.0.0',
            'title'             => 'Example App',
            'subtitle'          => 'A test app',
            'icon_url'          => 'https://example.com/icon.png',
        ]);

        $this->assertStringContainsString('<?xml version="1.0"', $xml);
        $this->assertStringContainsString('com.example.app', $xml);
        $this->assertStringContainsString('1.0.0', $xml);
        $this->assertStringContainsString('Example App', $xml);
        $this->assertStringContainsString('https://example.com/app.ipa', $xml);
    }

    public function test_builds_install_url(): void
    {
        $service = new ManifestService();
        $url = $service->buildInstallUrl('https://example.com/manifest.plist');

        $this->assertStringStartsWith('itms-services://', $url);
        $this->assertStringContainsString('https%3A%2F%2Fexample.com%2Fmanifest.plist', $url);
    }
}
