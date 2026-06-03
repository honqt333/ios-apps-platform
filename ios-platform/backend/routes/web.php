<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json([
    'name'    => config('app.name'),
    'version' => '1.0.0',
    'docs'    => '/api/v1',
    'health'  => '/up',
]));

Route::get('/manifests/{filename}', function (string $filename) {
    abort_unless(preg_match('/^[A-Za-z0-9._-]+\.plist$/', $filename), 404);
    $path = 'manifests/' . $filename;
    abort_unless(\Storage::disk('public')->exists($path), 404);
    return response(\Storage::disk('public')->get($path), 200, [
        'Content-Type' => 'application/xml',
    ]);
});
