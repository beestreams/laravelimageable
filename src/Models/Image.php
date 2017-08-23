<?php

namespace Beestreams\LaravelImageable\Models;

use Illuminate\Database\Eloquent\Model;
use Intervention\Image\ImageManager;
use Beestreams\LaravelImageable\Helpers\ImageResizer;
use Beestreams\LaravelImageable\Dispatchers\JobDispatcher;

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
        
        static::deleting(function($image){
            if (!isset($image->parent_image_id)) { // If is parent image, queue deletion of related sizes
                $jobDispatcher = new JobDispatcher();
                $jobDispatcher->deleteImageSizes($image->allSizes->pluck('id'));
            }
        });
        static::deleted(function($image){
            $image->deleteFile();
        });

    }

    // Relations
    public function allSizes()
    {
        return $this->hasMany(Image::class, 'parent_image_id');
    }

    // Shortcut to create file
    public static function createWithFile($model, $file, $props = []) 
    {
        $image = new Image;
        $image->setModel($model)
            ->setFile($file)
            ->setProperties($props)
            ->saveAndAttach();
        return $image;
    }

    // When image is saved attach to parent model
    public function saveAndAttach()
    {
        $this->imageable()->associate($this->model);
        $this->save();
        return $this;
    }

    // Create resized version of image
    public static function createResized($imageId, $sizeHandle)
    {
        $imageCopy = Image::find($imageId)->replicate();
        $imageCopy->size_handle = $sizeHandle;
        $imageCopy->parent_image_id = $imageId;
        $file = \Storage::disk(config('imageable.disk'))->get($imageCopy->path.'original/'.$imageCopy->name);
        $resizer = new ImageResizer($file);
        $configSize = config('imageable.sizes.'.$sizeHandle);
        $imageCopy->setFile($resizer->reSizeTo($configSize));
        $imageCopy->save();
        return $imageCopy;
    }

    // Set parent model for image
    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }

    // Return parent model
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
        $path = "{$this->path}{$this->size_handle}/{$this->name}";
//        $path = \Storage::disk(config('imageable.disk'))->url("{$this->path}{$this->size_handle}/{$this->name}");
        return $path;
    }

    public function getSourceFileAttribute()
    {
        return \Storage::get($this->sourcePath);
    }

    public function setProperties($props = [])
    {
        if (empty($this->file)) {
            $this->setFile($this->sourceFile);
        }
        $file = $this->file;
        
        $basePath = $this->model->imagePath.$this->model->id.'/';
        $defaultProps = [
            'name' => $this->sanitizeFileName($file->getClientOriginalName()),
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
