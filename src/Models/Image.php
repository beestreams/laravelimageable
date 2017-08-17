<?php

namespace Beestreams\LaravelImageable\Models;

use Illuminate\Database\Eloquent\Model;
use Intervention\Image\ImageManager;
use Beestreams\LaravelImageable\Helpers\ImageResizer;

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
        static::saving(function($image){
            $image->saveFile();
        });
        
        static::deleted(function($image){
            $image->deleteFile();
        });

    }

    public static function createWithFile($model, $file, $props = []) 
    {
        $image = new Image;
        $image->setModel($model)
            ->setFile($file)
            ->setProperties($props)
            ->saveAndAttach();
        return $image;
    }
    public function saveAndAttach()
    {
        $this->imageable()->associate($this->model);
        $this->save();
        return $this;
    }
    public static function createResized($imageId, $sizeHandle)
    {
        $imageCopy = Image::find($imageId)->replicate();
        $imageCopy->size_handle = $sizeHandle;
        $file = \Storage::disk(config('imageable.disk'))->get($imageCopy->path.'original/'.$imageCopy->name);
        $resizer = new ImageResizer($file);
        $configSize = config('imageable.sizes.'.$sizeHandle);
        $imageCopy->setFile($resizer->reSizeTo($configSize));
        $imageCopy->save();
        return $imageCopy;
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
    
    public function getSourcePathAttribute()
    {
        $path = \Storage::disk(config('imageable.disk'))->url("{$this->path}{$this->size_handle}/{$this->name}");
        return $path;
    }

    public function getSourceFileAttribute()
    {
        $path = substr($this->sourcePath, 8);
        return \Storage::get($path);
    }

    public function setProperties($props = [])
    {
        if (empty($this->file)) {
            $this->setFile($this->sourceFile);
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

    public function saveFile()
    {
        // make new imagemanager
        $imageManager = new ImageManager();

        // get file name if not set.
        if (empty($this->name)) {
            $this->name = $this->sanitizeFileName($file->name);
        }

        // Get path from config
        $configPath = config('filesystems.disks.'.config('imageable.disk').'.root');
        
        // Make directory if not set.
        \Storage::disk(config('imageable.disk'))->makeDirectory($this->path.'/'.$this->size_handle);
        
        // save file. Should be validated in request before save.
        $response = $imageManager
            ->make($this->file)
            ->save($configPath.'/'.$this->path.'/'.$this->size_handle.'/'.$this->name);
        return $this;
    }


    public function deleteFile()
    {
        \Storage::disk(config('imageable.disk'))->deleteDirectory($this->path.'/'.$this->size_handle);
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
