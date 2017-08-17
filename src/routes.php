<?php

Route::get('/images/{image}', function($imageId) {
    $image = Beestreams\LaravelImageable\Models\Image::find($imageId);
    return response()->download(storage_path('app/'.$image->sourcePath));
})->name('images.show');
