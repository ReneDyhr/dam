<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Models\File;
use Symfony\Component\HttpFoundation\StreamedResponse;

Route::get('/files/{slug}', function ($slug) {
    $extension = pathinfo($slug, PATHINFO_EXTENSION);
    $slug = pathinfo($slug, PATHINFO_FILENAME);
    $file = File::where('slug', $slug)->where('extension', $extension)->firstOrFail();
    $version = $file->versions()->where('status', 'active')->firstOrFail();
    $path = $version->path;
    $disk = Storage::disk('s3');

    // Check if the file exists
    if (!$disk->exists($path)) {
        return response(status: 404);
    }

    // Get the file's MIME type
    $mimeType = $disk->mimeType($path);

    $response = new StreamedResponse(function () use ($disk, $path): void {
        $stream = $disk->readStream($path);
        fpassthru($stream);
        fclose($stream);
    });

    $cacheTime = (int) $file->cache_time ?? 86400;
    $cacheTimeDate = now()->addSeconds($cacheTime);
    // Set headers
    $response->headers->set('Content-Type', $mimeType);
    $response->headers->set('Cache-Control', 'max-age=' . $cacheTime);

    $response->headers->set('Expires', $cacheTimeDate->toRfc7231String());

    return $response;
});
