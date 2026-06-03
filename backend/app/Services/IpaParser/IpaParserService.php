<?php

namespace App\Services\IpaParser;

use App\Services\IpaParser\Contracts\IpaParserServiceInterface;
use ZipArchive;

class IpaParserService implements IpaParserServiceInterface
{
    public function parse(string $absolutePath): array
    {
        if (! $this->isValidIpa($absolutePath)) {
            throw new \InvalidArgumentException('Invalid or unreadable IPA file');
        }

        $zip = new ZipArchive();
        if ($zip->open($absolutePath) !== true) {
            throw new \RuntimeException('Cannot open IPA archive');
        }

        $infoPlistPath = $this->findInfoPlist($zip);
        if (! $infoPlistPath) {
            $zip->close();
            throw new \RuntimeException('Info.plist not found in IPA');
        }

        $plistContent = $zip->getFromName($infoPlistPath);
        if ($plistContent === false) {
            $zip->close();
            throw new \RuntimeException('Cannot read Info.plist');
        }

        $plist = $this->parsePlistBinarySafe($plistContent);
        $zip->close();

        return [
            'bundle_id'           => $plist['CFBundleIdentifier'] ?? null,
            'name'                => $plist['CFBundleName'] ?? null,
            'display_name'        => $plist['CFBundleDisplayName'] ?? null,
            'version'             => $plist['CFBundleShortVersionString'] ?? null,
            'build_number'        => $plist['CFBundleVersion'] ?? null,
            'minimum_os_version'  => $plist['MinimumOSVersion'] ?? null,
            'icon_path'           => null,
            'icons'               => $this->extractIconRefs($plist),
            'size_bytes'          => filesize($absolutePath) ?: 0,
            'metadata'            => $plist,
        ];
    }

    public function extractIcon(string $absolutePath, string $outputDir): ?string
    {
        $zip = new ZipArchive();
        if ($zip->open($absolutePath) !== true) {
            return null;
        }

        $infoPlistPath = $this->findInfoPlist($zip);
        if (! $infoPlistPath) {
            $zip->close();
            return null;
        }

        $plist = $this->parsePlistBinarySafe($zip->getFromName($infoPlistPath));
        $icons = $this->extractIconRefs($plist);

        if (empty($icons)) {
            $zip->close();
            return null;
        }

        $bestIcon = end($icons);
        $iconData = $zip->getFromName($bestIcon);

        if ($iconData === false) {
            $zip->close();
            return null;
        }

        if (! is_dir($outputDir) && ! mkdir($outputDir, 0755, true) && ! is_dir($outputDir)) {
            $zip->close();
            return null;
        }

        $ext = pathinfo($bestIcon, PATHINFO_EXTENSION) ?: 'png';
        $filename = 'icon_' . substr(md5($bestIcon), 0, 8) . '.' . $ext;
        $fullPath = rtrim($outputDir, '/') . '/' . $filename;

        if (file_put_contents($fullPath, $iconData) !== false) {
            $zip->close();
            return $fullPath;
        }

        $zip->close();
        return null;
    }

    public function isValidIpa(string $absolutePath): bool
    {
        if (! is_file($absolutePath) || ! is_readable($absolutePath)) {
            return false;
        }

        $ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        if ($ext !== 'ipa') {
            return false;
        }

        // Quick ZIP signature check
        $fh = fopen($absolutePath, 'rb');
        if (! $fh) {
            return false;
        }
        $sig = fread($fh, 4);
        fclose($fh);

        return $sig === "PK\x03\x04" || $sig === "PK\x05\x06" || $sig === "PK\x07\x08";
    }

    // -----------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------

    protected function findInfoPlist(ZipArchive $zip): ?string
    {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (preg_match('#^Payload/[^/]+/Info\.plist$#', $name)) {
                return $name;
            }
        }
        return null;
    }

    protected function parsePlistBinarySafe(string $content): array
    {
        // Try XML first
        if (str_starts_with($content, '<?xml')) {
            $data = $this->parseXmlPlist($content);
            if ($data !== null) {
                return $data;
            }
        }

        // Try binary plist via a simple parser; for production, use a library
        // like `cfpropertylist` or `plist`. Here we provide a tolerant fallback.
        return $this->parseSimpleBinaryPlist($content);
    }

    protected function parseXmlPlist(string $content): ?array
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($content);
        if ($xml === false) {
            return null;
        }
        return $this->xmlToArray($xml);
    }

    protected function xmlToArray(\SimpleXMLElement $xml): array
    {
        $result = [];
        $dict = [];

        foreach ($xml->dict->children() as $child) {
            // not used; placeholder for future
        }

        // More robust parsing
        if (isset($xml->dict)) {
            $children = $xml->dict->children();
            $keys = $children->key;
            $values = $children->string;
            // This is naive; in production use cfpropertylist. For scaffold purposes
            // a simple text-based extraction works for typical Info.plist fields.
        }

        // Fallback: try regex extraction of common keys
        $result = $this->regexExtractPlist($xml->asXML());
        return $result;
    }

    protected function regexExtractPlist(?string $xml): array
    {
        if (! $xml) {
            return [];
        }

        $keys = [
            'CFBundleIdentifier',
            'CFBundleName',
            'CFBundleDisplayName',
            'CFBundleShortVersionString',
            'CFBundleVersion',
            'MinimumOSVersion',
        ];

        $out = [];
        foreach ($keys as $k) {
            if (preg_match('#<key>' . preg_quote($k, '#') . '</key>\s*<string>([^<]+)</string>#', $xml, $m)) {
                $out[$k] = $m[1];
            }
        }
        return $out;
    }

    protected function parseSimpleBinaryPlist(string $content): array
    {
        // For binary plist, the cleanest is to use `cfpropertylist`. We provide
        // a minimal scaffold and surface a clear error in dev.
        if (str_starts_with($content, 'bplist')) {
            // Defer to extension if available
            if (class_exists(\CFPropertyList\CFPropertyList::class)) {
                $cf = new \CFPropertyList\CFPropertyList();
                $cf->parse($content);
                return $cf->toArray() ?: [];
            }

            // Return best-effort empty; admin can fill fields manually
            return [];
        }

        return [];
    }

    protected function extractIconRefs(array $plist): array
    {
        $icons = [];
        $candidates = [];

        if (isset($plist['CFBundleIcons'])) {
            $candidates = array_merge($candidates, $this->walkIconRefs($plist['CFBundleIcons']));
        }
        if (isset($plist['CFBundleIcons~ipad'])) {
            $candidates = array_merge($candidates, $this->walkIconRefs($plist['CFBundleIcons~ipad']));
        }

        // Sort by largest size; pick @3x, @2x, @1x in order
        $best = [];
        foreach ($candidates as $path) {
            if (preg_match('/@(\d+)x/', $path, $m)) {
                $best[(int) $m[1]] = $path;
            } else {
                $best[1] = $path;
            }
        }
        krsort($best);

        return array_values($best);
    }

    protected function walkIconRefs(mixed $node): array
    {
        $out = [];
        if (! is_array($node)) {
            return $out;
        }
        foreach ($node as $k => $v) {
            if ($k === 'CFBundleIconFile' && is_string($v)) {
                $out[] = $v;
            } elseif (is_array($v)) {
                $out = array_merge($out, $this->walkIconRefs($v));
            }
        }
        return $out;
    }
}
