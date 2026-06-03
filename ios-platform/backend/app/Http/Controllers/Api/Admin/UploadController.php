<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Upload\UploadIconRequest;
use App\Http\Requests\Upload\UploadIpaRequest;
use App\Http\Requests\Upload\UploadScreenshotRequest;
use App\Models\App as AppModel;
use App\Models\AppFile;
use App\Models\Screenshot;
use App\Services\Audit\Contracts\AuditServiceInterface;
use App\Services\IpaParser\Contracts\IpaParserServiceInterface;
use App\Services\Manifest\Contracts\ManifestServiceInterface;
use App\Services\Storage\Contracts\StorageServiceInterface;
use Illuminate\Http\JsonResponse;

class UploadController extends Controller
{
    public function __construct(
        protected StorageServiceInterface $storage,
        protected IpaParserServiceInterface $ipaParser,
        protected ManifestServiceInterface $manifest,
        protected AuditServiceInterface $audit,
    ) {
    }

    public function ipa(UploadIpaRequest $request): JsonResponse
    {
        $app = AppModel::findOrFail($request->integer('app_id'));
        $file = $request->file('ipa');

        $disk = config('platform.storage.default_disk', 'local');
        $stored = $this->storage->store($file, "apps/{$app->id}/ipa", null, $disk);

        $metadata = [];
        if (config('platform.ipa.parse_on_upload', true) && $stored['disk'] === 'local') {
            try {
                $absolutePath = \Storage::disk($stored['disk'])->path($stored['path']);
                $metadata = $this->ipaParser->parse($absolutePath);
            } catch (\Throwable $e) {
                $metadata = ['metadata_error' => $e->getMessage()];
            }
        }

        AppFile::where('app_id', $app->id)->update(['is_current' => false]);

        $appFile = AppFile::create([
            'app_id'          => $app->id,
            'version'         => $metadata['version'] ?? $app->version,
            'build_number'    => $metadata['build_number'] ?? null,
            'disk'            => $stored['disk'],
            'path'            => $stored['path'],
            'size_bytes'      => $stored['size'],
            'checksum_sha256' => $this->storage->checksum($stored['path'], $stored['disk']),
            'metadata'        => $metadata,
            'is_current'      => true,
        ]);

        $manifestPath = $this->manifest->generate($app, $appFile);
        $appFile->manifest_path = $manifestPath;
        $appFile->save();

        $app->ipa_path       = $stored['path'];
        $app->ipa_disk       = $stored['disk'];
        $app->manifest_path  = $manifestPath;
        $app->ipa_size_bytes = $stored['size'];

        if (! empty($metadata['version'])) {
            $app->version = $metadata['version'];
        }
        if (! empty($metadata['build_number'])) {
            $app->build_number = $metadata['build_number'];
        }
        if (! empty($metadata['minimum_os_version'])) {
            $app->minimum_ios_version = $metadata['minimum_os_version'];
        }
        $app->save();

        $this->audit->log('app.ipa_uploaded', $app, $request->user(), [
            'file_id' => $appFile->id,
            'size'    => $stored['size'],
        ]);

        return response()->json([
            'success' => true,
            'message' => __('apps.ipa_uploaded'),
            'data'    => [
                'app'      => $app->fresh()->only(['id', 'name', 'slug', 'version', 'manifest_path']),
                'file'     => $appFile,
                'install_url' => $app->install_url,
            ],
        ], 201);
    }

    public function icon(UploadIconRequest $request): JsonResponse
    {
        $app = AppModel::findOrFail($request->integer('app_id'));

        $disk = config('platform.storage.public_disk', 'public');

        if ($app->icon_path) {
            $this->storage->delete($app->icon_path, $disk);
        }

        $stored = $this->storage->store($request->file('icon'), "apps/{$app->id}/icon", null, $disk);

        $app->icon_path = $stored['path'];
        $app->save();

        $this->audit->log('app.icon_uploaded', $app, $request->user());

        return response()->json([
            'success' => true,
            'message' => __('apps.icon_uploaded'),
            'data'    => [
                'icon_path' => $stored['path'],
                'icon_url'  => $stored['url'],
            ],
        ]);
    }

    public function screenshots(UploadScreenshotRequest $request): JsonResponse
    {
        $app = AppModel::findOrFail($request->integer('app_id'));
        $disk = config('platform.storage.public_disk', 'public');
        $baseOrder = $app->screenshots()->count();

        $created = [];
        foreach ($request->file('screenshots') as $i => $file) {
            $stored = $this->storage->store($file, "apps/{$app->id}/screenshots", null, $disk);
            $created[] = Screenshot::create([
                'app_id'     => $app->id,
                'path'       => $stored['path'],
                'disk'       => $disk,
                'sort_order' => $baseOrder + $i,
            ]);
        }

        $this->audit->log('app.screenshots_uploaded', $app, $request->user(), [
            'count' => count($created),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('apps.screenshots_uploaded'),
            'data'    => $created,
        ], 201);
    }
}
