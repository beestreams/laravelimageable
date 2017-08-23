<?php

namespace Beestreams\LaravelImageable\Jobs;

use Beestreams\LaravelImageable\Models\Image;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteImage implements ShouldQueue
{
    public $imageId;
    public $sizeHandle;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($imageId)
    {
        $this->imageId = $imageId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $image = Image::findOrFail($this->imageId)->delete();
        return $this;
    }
}
