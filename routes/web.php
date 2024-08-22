<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Models\File;
use Symfony\Component\HttpFoundation\StreamedResponse;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/files/{slug}', function ($slug) {
    $file = File::where('slug', $slug)->firstOrFail();
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

    // Set headers
    $response->headers->set('Content-Type', $mimeType);
    $response->headers->set('Cache-Control', 'max-age=86400');
    $response->headers->set('Expires', now()->addDay()->toRfc7231String());

    return $response;
});
