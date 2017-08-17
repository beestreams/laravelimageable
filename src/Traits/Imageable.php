<?php

namespace Beestreams\LaravelImageable\Traits;

use Beestreams\LaravelImageable\Models\Image; 
use Beestreams\LaravelImageable\Dispatchers\JobDispatcher;

trait Imageable
{
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
}
