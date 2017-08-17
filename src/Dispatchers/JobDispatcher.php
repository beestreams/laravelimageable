<?php 
namespace Beestreams\LaravelImageable\Dispatchers;

use Beestreams\LaravelImageable\Jobs\ResizeImage;


class JobDispatcher
{
    public function queueImageSizes($imageId)
    {
        foreach (config('imageable.sizes') as $size => $dimensions) {
            ResizeImage::dispatch($imageId, $size);
        }
        return $this;
    }
}
