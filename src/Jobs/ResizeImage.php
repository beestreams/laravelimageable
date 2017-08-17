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
    public $imageId;
    public $sizeHandle;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($imageId, $sizeHandle)
    {
        $this->imageId = $imageId;
        // $this->imageSizes = config('imageable.sizes'); // Check if sizeHandle exist in config sizes
        $this->sizeHandle = $sizeHandle;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $imageModel = Image::findOrFail($this->imageId);
        $imageModel->createResized($imageModel, $this->sizeHandle);
    }
}
