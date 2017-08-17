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
    public function addFile($file)
    {
        // If not image, return
        // $file->extension check;
        if (empty($file)) {
            return false;
        }
        // 1. Create image model and save to parent model
        $imageModel = new Image();
        $imageModel->setProperties($file);
        $this->images()->save($imageModel);
        
        // 2. Create file and save to disk
        $filePath = $imageModel->path; // {model: filepath}/{modelId}
        $fileName = $imageModel->name;



        // 3. Dispatch jobs for image sizes
        $jobDispatcher = new JobDispatcher();
        $jobDispatcher->queueImageSizes($imageModel->id);

        return $this;
    }

    /**
    * Get all of the categories for the categorable model.
    */
    public function images()
    {
        return $this->morphToMany(Image::class, 'imageable');
    }
}
