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
    private $file;
    
    public static function boot()
    {
        parent::boot();

        static::saved(function($image)
        {
            $image->saveFile();
        });
    }

    public static function createWithFile($model, $file, $props = []) 
    {
        $image = new Image;
        $image->setModel($model)
            ->setFile($file)
            ->setProperties($props)
            ->save();
        return $image;
    }

    public static function createResized($imageId, $sizeHandle)
    {
        $imageCopy = Image::find($imageId)->replicate();
        $imageCopy->size_handle = $sizeHandle;
        $file = \Storage::disk(config('imageable.disk'))->get($path.'original/'.$imageCopy->name);
        $resizer = new ImageResizer($file);
        $imageCopy->setFile($resizer->reSizeTo($configSize));
        $imageCopy->save();
    }

    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }

    public function getModelAttribute()
    {
        return $this->model;
    }

    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    public function getFileSourceAttribute()
    {
        return \Storage::disk(config('imageable.disk'))->get("{$this->path}{$this->size_handle}/{$this->name}");
    }

    public function setProperties($props = [])
    {
        if (empty($this->file)) {
            $this->setFile($this->fileSource);
        }
        $file = $this->file;
        
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
        $imageManager->make($this->file)->save($path.'/'.$this->path.'/'.$this->sizeHandle.'/'.$this->name);

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
