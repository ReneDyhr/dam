<?php

use Illuminate\Support\Facades\Route;
use App\Models\File;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/files/{slug}', function ($slug) {
    $file = File::where('slug', $slug)->firstOrFail();
    $version = $file->versions()->where('status', 'active')->firstOrFail();
    $path = storage_path('app/public/' . $version->path);

    // Check if the file exists
    if (!file_exists($path)) {
        abort(404);
    }

    // Determine the file's MIME type
    $mimeType = mime_content_type($path);

    // Create a response with the file
    return response()->file($path, [
        'Content-Type' => $mimeType
    ]);
});
