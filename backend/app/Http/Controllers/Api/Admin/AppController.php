<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Apps\StoreAppRequest;
use App\Http\Requests\Apps\UpdateAppRequest;
use App\Http\Resources\AppResource;
use App\Models\App as AppModel;
use App\Models\AppFile;
use App\Models\Screenshot;
use App\Repositories\Contracts\AppRepositoryInterface;
use App\Services\Audit\Contracts\AuditServiceInterface;
use App\Services\IpaParser\Contracts\IpaParserServiceInterface;
use App\Services\Manifest\Contracts\ManifestServiceInterface;
use App\Services\Storage\Contracts\StorageServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AppController extends Controller
{
    public function __construct(
        protected AppRepositoryInterface $apps,
        protected StorageServiceInterface $storage,
        protected IpaParserServiceInterface $ipaParser,
        protected ManifestServiceInterface $manifest,
        protected AuditServiceInterface $audit,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'q'          => $request->query('q'),
            'category'   => $request->query('category'),
            'developer'  => $request->query('developer'),
            'is_archived' => $request->query('is_archived'),
            'is_featured' => $request->query('is_featured'),
            'sort'       => $request->query('sort', 'newest'),
            'admin'      => true,
        ];

        $perPage = (int) min((int) $request->query('per_page', 20), 100);
        $paginator = $this->apps->paginateWithFilters($filters, $perPage);

        return response()->json([
            'success' => true,
            'data'    => AppResource::collection($paginator->items())->resolve(),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    public function store(StoreAppRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['created_by'] = $request->user()?->id;
        $data['updated_by'] = $request->user()?->id;

        return DB::transaction(function () use ($request, $data) {
            $app = $this->apps->create($data);

            if ($request->hasFile('icon')) {
                $stored = $this->storage->store(
                    $request->file('icon'),
                    "apps/{$app->id}/icon",
                    null,
                    config('platform.storage.public_disk', 'public')
                );
                $app->icon_path = $stored['path'];
                $app->save();
            }

            if ($request->hasFile('screenshots')) {
                $disk = config('platform.storage.public_disk', 'public');
                foreach ($request->file('screenshots') as $i => $file) {
                    $stored = $this->storage->store($file, "apps/{$app->id}/screenshots", null, $disk);
                    Screenshot::create([
                        'app_id'     => $app->id,
                        'path'       => $stored['path'],
                        'disk'       => $disk,
                        'sort_order' => $i,
                    ]);
                }
            }

            if ($request->hasFile('ipa')) {
                $this->handleIpaUpload($app, $request->file('ipa'), $request->user()->id);
            }

            $this->audit->log('app.created', $app, $request->user());

            return response()->json([
                'success' => true,
                'message' => __('apps.created'),
                'data'    => (new AppResource($app->fresh(['category', 'screenshots', 'files'])))->resolve(),
            ], 201);
        });
    }

    public function show(string $app): JsonResponse
    {
        $model = $this->apps->findByIdOrSlug($app);

        if (! $model) {
            return response()->json([
                'success' => false,
                'message' => __('errors.app_not_found'),
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => (new AppResource($model->load(['category', 'screenshots', 'files'])))->resolve(),
        ]);
    }

    public function update(UpdateAppRequest $request, string $app): JsonResponse
    {
        $model = $this->apps->findByIdOrSlug($app);
        if (! $model) {
            return response()->json([
                'success' => false,
                'message' => __('errors.app_not_found'),
            ], 404);
        }

        $data = $request->validated();
        $data['updated_by'] = $request->user()?->id;

        return DB::transaction(function () use ($request, $model, $data) {
            $model->fill($data);
            $model->save();

            if ($request->hasFile('icon')) {
                if ($model->icon_path) {
                    $this->storage->delete($model->icon_path, $model->icon_path_disk ?? config('platform.storage.public_disk', 'public'));
                }
                $stored = $this->storage->store(
                    $request->file('icon'),
                    "apps/{$model->id}/icon",
                    null,
                    config('platform.storage.public_disk', 'public')
                );
                $model->icon_path = $stored['path'];
                $model->save();
            }

            if ($request->hasFile('screenshots')) {
                $disk = config('platform.storage.public_disk', 'public');
                $baseOrder = $model->screenshots()->count();
                foreach ($request->file('screenshots') as $i => $file) {
                    $stored = $this->storage->store($file, "apps/{$model->id}/screenshots", null, $disk);
                    Screenshot::create([
                        'app_id'     => $model->id,
                        'path'       => $stored['path'],
                        'disk'       => $disk,
                        'sort_order' => $baseOrder + $i,
                    ]);
                }
            }

            if ($request->hasFile('ipa')) {
                $this->handleIpaUpload($model, $request->file('ipa'), $request->user()->id);
            }

            $this->audit->log('app.updated', $model, $request->user());

            return response()->json([
                'success' => true,
                'message' => __('apps.updated'),
                'data'    => (new AppResource($model->fresh(['category', 'screenshots', 'files'])))->resolve(),
            ]);
        });
    }

    public function destroy(Request $request, string $app): JsonResponse
    {
        $model = $this->apps->findByIdOrSlug($app);
        if (! $model) {
            return response()->json([
                'success' => false,
                'message' => __('errors.app_not_found'),
            ], 404);
        }

        $this->authorize('delete', $model);

        $this->audit->log('app.deleted', $model, $request->user());
        $model->delete();

        return response()->json([
            'success' => true,
            'message' => __('apps.deleted'),
        ]);
    }

    public function archive(Request $request, string $app): JsonResponse
    {
        $model = $this->apps->findByIdOrSlug($app);
        if (! $model) {
            return response()->json([
                'success' => false,
                'message' => __('errors.app_not_found'),
            ], 404);
        }

        $this->authorize('archive', $model);

        $model->is_archived = ! $model->is_archived;
        $model->save();

        $this->audit->log($model->is_archived ? 'app.archived' : 'app.unarchived', $model, $request->user());

        return response()->json([
            'success' => true,
            'message' => $model->is_archived ? __('apps.archived') : __('apps.unarchived'),
            'data'    => (new AppResource($model))->resolve(),
        ]);
    }

    public function toggleActive(Request $request, string $app): JsonResponse
    {
        $model = $this->apps->findByIdOrSlug($app);
        if (! $model) {
            return response()->json([
                'success' => false,
                'message' => __('errors.app_not_found'),
            ], 404);
        }

        $this->authorize('publish', $model);

        $model->is_active = ! $model->is_active;
        $model->save();

        $this->audit->log('app.toggled', $model, $request->user(), ['is_active' => $model->is_active]);

        return response()->json([
            'success' => true,
            'message' => $model->is_active ? __('apps.activated') : __('apps.deactivated'),
            'data'    => (new AppResource($model))->resolve(),
        ]);
    }

    // -----------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------

    protected function handleIpaUpload(AppModel $app, \Illuminate\Http\UploadedFile $file, int $userId): void
    {
        $disk = config('platform.storage.default_disk', 'local');
        $stored = $this->storage->store(
            $file,
            "apps/{$app->id}/ipa",
            null,
            $disk
        );

        // Parse metadata
        $absolutePath = $stored['disk'] === 'local'
            ? \Storage::disk($stored['disk'])->path($stored['path'])
            : $stored['path'];

        $metadata = [];
        if (config('platform.ipa.parse_on_upload', true) && $stored['disk'] === 'local') {
            try {
                $metadata = $this->ipaParser->parse($absolutePath);
            } catch (\Throwable $e) {
                $metadata = ['metadata_error' => $e->getMessage()];
            }
        }

        // Mark previous file as not current
        AppFile::where('app_id', $app->id)->update(['is_current' => false]);

        $appFile = AppFile::create([
            'app_id'         => $app->id,
            'version'        => $metadata['version'] ?? $app->version,
            'build_number'   => $metadata['build_number'] ?? null,
            'disk'           => $stored['disk'],
            'path'           => $stored['path'],
            'size_bytes'     => $stored['size'],
            'checksum_sha256' => $this->storage->checksum($stored['path'], $stored['disk']),
            'metadata'       => $metadata,
            'is_current'     => true,
        ]);

        // Generate manifest
        $manifestPath = $this->manifest->generate($app, $appFile);
        $appFile->manifest_path = $manifestPath;
        $appFile->save();

        // Update app top-level fields
        $app->ipa_path        = $stored['path'];
        $app->ipa_disk        = $stored['disk'];
        $app->manifest_path   = $manifestPath;
        $app->ipa_size_bytes  = $stored['size'];
        $app->file_size_human = $this->humanSize($stored['size']);

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
    }

    protected function humanSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
