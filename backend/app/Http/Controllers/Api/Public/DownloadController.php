<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Repositories\Contracts\DownloadRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadController extends Controller
{
    public function __construct(protected DownloadRepositoryInterface $downloads)
    {
    }

    public function track(Request $request, string $app): JsonResponse
    {
        $model = App::query()->where('slug', $app)->orWhere('id', $app)->first();

        if (! $model || ! $model->isInstallable()) {
            return response()->json([
                'success' => false,
                'message' => __('errors.app_not_installable'),
            ], 404);
        }

        $model->incrementDownloads();

        $this->downloads->record([
            'app_id'      => $model->id,
            'user_id'     => optional($request->user())->id,
            'app_file_id' => optional($model->currentFile)->id,
            'version'     => $model->version,
            'ip_address'  => $request->ip(),
            'user_agent'  => substr((string) $request->userAgent(), 0, 500),
            'device_id'   => $request->header('X-Device-Id'),
            'status_code' => 200,
        ]);

        return response()->json([
            'success'     => true,
            'data'        => [
                'install_url' => $model->install_url,
                'manifest_url' => $model->manifest_path
                    ? rtrim(config('platform.manifest.base_url'), '/') . '/' . ltrim($model->manifest_path, '/')
                    : null,
            ],
        ]);
    }

    public function download(Request $request, string $app): StreamedResponse|JsonResponse
    {
        $model = App::query()->where('slug', $app)->orWhere('id', $app)->first();

        if (! $model || ! $model->isInstallable()) {
            return response()->json([
                'success' => false,
                'message' => __('errors.app_not_installable'),
            ], 404);
        }

        $file = $model->currentFile ?? $model->files()->latest('created_at')->first();
        if (! $file) {
            return response()->json([
                'success' => false,
                'message' => __('errors.no_file'),
            ], 404);
        }

        $disk = Storage::disk($file->disk);

        if (! $disk->exists($file->path)) {
            return response()->json([
                'success' => false,
                'message' => __('errors.file_missing'),
            ], 404);
        }

        $model->incrementDownloads();

        $this->downloads->record([
            'app_id'      => $model->id,
            'user_id'     => optional($request->user())->id,
            'app_file_id' => $file->id,
            'version'     => $file->version,
            'ip_address'  => $request->ip(),
            'user_agent'  => substr((string) $request->userAgent(), 0, 500),
            'status_code' => 200,
            'completed_at' => now(),
        ]);

        return $disk->download($file->path, "{$model->slug}_{$file->version}.ipa");
    }
}
