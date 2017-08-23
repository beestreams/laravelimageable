<?php
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Database\Eloquent\Model;
use Beestreams\LaravelImageable\Models\Image;
use Beestreams\LaravelImageable\Traits\Imageable;
use Beestreams\LaravelImageable\Helpers\ImageResizer;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Beestreams\LaravelImageable\Jobs\ResizeImage;

class IntegrationTest extends TestCase
{
    use DatabaseMigrations;

    private $exampleModel;

    public function setUp()
    {
        //  ../../../vendor/bin/phpunit
        require './vendor/autoload.php';
        parent::setUp();
        $this->exampleModel();
    }

    /**
     * Set up model that categories are attached to
     */
    private function exampleModel()
    {
        $this->exampleModel = new ExModelObject();
        $this->exampleModel->id = 1;
    }

    protected function createOriginal()
    {
        $newName = 'noavatar.jpg';
        $file = UploadedFile::fake()->image('avatar.jpg', 2000, 2000)->size(100);
        $image = Image::createWithFile($this->exampleModel, $file);
        return $image;
    }

    /** @test */
    public function model_is_set_on_image()
    {
        $model = new Image();
        $model->setModel($this->exampleModel);
        $this->assertTrue($model->model instanceof ExModelObject); 
    }

    /** @test */
    public function image_has_access_to_file_source_path ()
    {
        $image = $this->createOriginal(); 
        $image->sourcePath;
        $this->assertTrue(!empty($image->sourcePath));
        $image->delete();
    }

    /** @test */
    public function file_is_saved_to_disk_as_original()
    {
        $newName = 'noavatar.jpg';
        $file = UploadedFile::fake()->image('avatar.jpg');
        $image = Image::createWithFile($this->exampleModel, $file);
        $this->assertTrue(!empty($image->sourceFile));
        $image->delete();
    }

    /** @test */
    public function file_is_deleted_on_model_delete ()
    {
        $this->expectException('Illuminate\Contracts\Filesystem\FileNotFoundException'); 
        $image = $this->createOriginal(); 
        $path = $image->sourcePath;
        $image->delete();
        $empty = \Storage::get(substr($path, 8));
    }

    /** @test */
    public function image_model_is_populated()
    {
        $size = 'original';
        $file = UploadedFile::fake()->image('avatar.jpg', 2000, 2000);
        
        $model = new Image();
        $file->alt_text = 'Alt text test';
        $file->description = 'Image description';
        
        $calculatedPath = "{$this->exampleModel->imagePath}{$this->exampleModel->id}/";
        $model->setModel($this->exampleModel);
        $model->setFile($file);
        $model->setProperties();
        $this->assertTrue(strpos($model->path, $this->exampleModel->imagePath) !== false);
        $this->assertEquals($file->description, $model->description);
        $this->assertEquals($file->alt_text, $model->alt_text);
        $this->assertEquals($calculatedPath, $model->path);
        $this->assertEquals($size, $model->size_handle);
        $this->assertEquals($file->getClientSize(), $model->size);
        $this->assertEquals(studly_case($file->name), $model->name);
        $this->assertEquals($file->getMimeType(), $model->mime_type);
    }

    /** @test */
    public function file_is_resized()
    {
        // Setup fake file, parent model and config path
        $image = $this->createOriginal();
        $configSize = config('imageable.sizes.small');
        $resizedImage = $image->createResized($image->id, 'small');
        $sourceFile = $resizedImage->sourceFile;
        $this->assertTrue(!empty($sourceFile));
        $resizedImage->delete();
    }

    /** @test */
    public function all_sizes_are_deleted_when_original_is_deleted ()
    {
        // This test makes no sense. The delete method is triggered in a job. Should check for queue
        $image = $this->createOriginal();
        $configSize = config('imageable.sizes.small');
        $resizedImage = $image->createResized($image->id, 'small');
        $image->delete();
        $allImages = $this->exampleModel->images; // createOriginal makes image on exampleModel
        $this->assertTrue($allImages->isEmpty()); 
    }

    /** @test */
    public function when_parent_gets_file_attached_the_file_is_saved ()
    {
        $parent = $this->exampleModel;
        
        $file = UploadedFile::fake()->image('avatar.jpg', 2000, 2000)->size(100);
        $image = Image::createWithFile($this->exampleModel, $file);
        $parent->attachImage($file);
        $this->assertTrue(!empty($parent->images->first()->sourceFile));

    }
    
    /** @test */
    public function resize_jobs_are_dispatched_to_queue ()
    {
        Bus::fake();
        
        // When a model gets a file attached
        $parent = $this->exampleModel;
        
        $file = UploadedFile::fake()->image('avatar.jpg', 2000, 2000)->size(100);

        $parent->attachImage($file);
        
        // And that file is saved
        // Available sizes should be generated from queue
        $image = $parent->images->first();
        // Perform order shipping...
        
        Bus::assertDispatched(ResizeImage::class, function ($job) use ($image) {
            return $job->imageId === $image->id;
        });

        // Assert a job was not dispatched...
        Bus::assertNotDispatched(AnotherJob::class); 
    }



}

/**
* Parent
*/
class ExModelObject extends Model
{
    use Imageable;
    public $imagePath = 'basepath/';
}
