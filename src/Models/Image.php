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
        'size_handle',
        'mime_type',
    ];
    private $model;

    public static function createWithFile($model, $file, $props = []) 
    {
        $image = new Image;
        $image->setModel($model)
            ->setProperties($file, $props)
            ->saveFile($file)
            ->persist();
    }

    public static function createResized($imageId, $sizeHandle)
    {
        $imageCopy = Image::find($imageId)->replicate();
        $resizer = new ImageResizer($file);
        $savePath = $imageCopy->path.$sizeHandle;
        $resizedImage = $resizer->reSizeTo($configSize)->saveTo($savePath); // The important line
    }

    public function persist()
    {
        $this->imageable()->associate($this->model);
    }

    public function setModel($model)
    {
        $this->model = $model;
    }

    public function getModelAttribute()
    {
        return $this->model;
    }

    public function setProperties($file, $props = [])
    {
        $basePath = $this->model->uploadPath.'/'.$this->model->id.'/';
        $defaultProps = [
            'name' => $this->sanitizeFileName($file->name),
            'size_handle' => 'original',
            'alt_text' => $file->alt_text ?? '',
            'description' => $file->description ?? '',
            'mime_type' => $file->getMimeType(),
            'size' => $file->getClientSize(),
            'size_handle' => 'original',
            'path' => $basePath
        ];
        $props = array_merge($defaultProps, $props);

        $this->fill($props);

        return $this;
    }

    public function sanitizeFileName($name)
    {
        return studly_case($name);
    }

    public function saveFile($file)
    {
        // size variable
        if (!$this->sizeHandle) {
            $this->sizeHandle = 'original';
        }

        // make new imagemanager
        $imageManager = new ImageManager();

        // get file name if not set.
        if (empty($this->name)) {
            $this->name = $this->sanitizeFileName($file->name);
        }

        // Get path from config
        $path = config('filesystems.disks.'.config('imageable.disk').'.root');
        
        // Make directory if not set.
        \Storage::disk(config('imageable.disk'))->makeDirectory($this->path.'/'.$this->sizeHandle);
        
        // save file. Should be validated in request before save.
        $imageManager->make($file)->save($path.'/'.$this->path.'/'.$this->sizeHandle.'/'.$this->name);

        return $this;
    }
    /**
     * Example model relation
     */
    public function imageable()
    {
    	return $this->morphTo();
    }
}
