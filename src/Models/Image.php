<?php

namespace Beestreams\LaravelImageable\Models;

use Illuminate\Database\Eloquent\Model;
use Intervention\Image\ImageManager;


class Image extends Model
{
    protected $fillable = [
        'name',
        'description',
        'alt_text',
        'path',
        'size',
    ];


    public function setProperties($file, $variant = null)
    {
        $this->name = $this->sanitizeFileName($file->name);
    }

    public function sanitizeFileName($name)
    {
        return studly_case($name);
    }

    public function saveFile($file)
    {
        // size variable
        if (!$this->size) {
            $this->size = 'original';
        }

        // make new imagemanager
        $imageManager = new ImageManager();

        // get file name if not set.
        if (empty($this->name)) {
            $this->name = $file->name;
        }

        // Get path from config
        $path = config('filesystems.disks.'.config('imageable.disk').'.root');
        
        // Make directory if not set.
        \Storage::disk(config('imageable.disk'))->makeDirectory($this->path.'/'.$this->size);
        
        // save file. Should be validated before save.
        $imageManager->make($file)->save($path.'/'.$this->path.'/'.$this->size.'/'.$this->name);

        return $this;
    }
    /**
     * Example model relation
     */
    // public function model()
    // {
    // 	return $this->morphedByMany(Model::class, 'categorizable');
    // }
}
