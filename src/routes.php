<?php

Route::get('/images/{image}', function($imageId) {
    $image = Beestreams\LaravelImageable\Models\Image::find($imageId);
    return response()->download(storage_path('app/'.$image->sourcePath));
})->name('images.show');

Route::delete('/images/{image}/delete', function($imageId) {
    $image = Beestreams\LaravelImageable\Models\Image::findOrFail($imageId);
    $image->delete();
    return redirect()->back();
})->name('images.delete');
