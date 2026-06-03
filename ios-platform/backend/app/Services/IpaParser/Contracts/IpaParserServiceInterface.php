<?php

namespace App\Services\IpaParser\Contracts;

interface IpaParserServiceInterface
{
    /**
     * Parse an IPA file and extract metadata.
     *
     * @return array{
     *     bundle_id: ?string,
     *     name: ?string,
     *     display_name: ?string,
     *     version: ?string,
     *     build_number: ?string,
     *     minimum_os_version: ?string,
     *     icon_path: ?string,
     *     icons: array<int, string>,
     *     size_bytes: int,
     *     metadata: array
     * }
     */
    public function parse(string $absolutePath): array;

    public function extractIcon(string $absolutePath, string $outputDir): ?string;

    public function isValidIpa(string $absolutePath): bool;
}
