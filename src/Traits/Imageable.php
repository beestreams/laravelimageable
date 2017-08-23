<?php

namespace Beestreams\LaravelImageable\Traits;

use Beestreams\LaravelImageable\Models\Image; 
use Beestreams\LaravelImageable\Dispatchers\JobDispatcher;

trait Imageable
{
    public static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            $originalImages = $model->images->where('size_handle', 'original');
            foreach ($originalImages as $image) {
                $image->delete();
            }
        });
    }
    /**
     * Add or create single category
     * @param String $name The Category name to be added
     */
    public function attachImage($file)
    {
        // If not image, return
        // $file->extension check;
        if (empty($file)) {
            return false;
        }
        // 1. Create image model and save to parent model
        $image = Image::createWithFile($this, $file);

        // 2. Dispatch jobs for image sizes
        $jobDispatcher = new JobDispatcher();
        $jobDispatcher->queueImageSizes($image->id);

        return $this;
    }

    /**
     * Get all of the categories for the categorable model.
     */
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function original()
    {
        return $this->belongsTo(Image::class);
    }

}
