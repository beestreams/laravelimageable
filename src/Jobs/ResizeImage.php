<?php

namespace Beestreams\LaravelImageable\Jobs;

use Beestreams\LaravelImageable\Models\Image;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ResizeImage implements ShouldQueue
{
    private $imageModelId;
    private $imageSizes;
    private $sizeHandle;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($imageModelId, $sizeHandle)
    {
        $this->imageModelId = $imageModelId;
        $this->imageSizes = config('imageable.sizes');
        $this->sizeHandle = $sizeHandle;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $imageModel = Image::findOrFail($this->imageModelId);
        $imageModel->size_handle = $this->sizeHandle;
        $resizer = new ImageResizer($basePath);
    }
}
