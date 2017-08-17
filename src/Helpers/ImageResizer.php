<?php

namespace Beestreams\LaravelImageable\Helpers;

use Intervention\Image\ImageManager;

class ImageResizer
{
    private $file;
    private $imageManager;

    
    
    function __construct($file)
    {
        $this->imageManager = new ImageManager();
        $this->file = $file;
    }

    public function reSizeTo($dimensions)
    {
        extract($dimensions); // [width, height]
        // Crop image to dimensions
        if( isset($width) && isset($height) ) {
            $this->file = $this->imageManager
                ->make( $this->file )
                ->fit($width, $height, function($constraint){
                    $constraint->upSize();
                });
            return $this->file;
        }

        // Resize with null value, preserve aspect ratio
        $this->file = $this->imageManager->make( $this->file )
            ->resize($width, $height, function($constraint){
                $constraint->aspectRatio();
            });
        return $this->file;
    }

    public function saveTo($path)
    {
        return $this->file->save($path);
    }

}
