<?php
use Beestreams\LaravelImageable\Helpers\ImageResizer;
use Beestreams\LaravelImageable\Models\Image;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

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

    /** @test */
    public function model_is_set_on_image()
    {
        $model = new Image();
        $model->setModel($this->exampleModel);
        $this->assertTrue($model->model instanceof ExModelObject); 
    }

    /** @test */
    public function file_is_saved_to_disk_as_original()
    {
        $newName = 'noavatar.jpg';
        $file = UploadedFile::fake()->image('avatar.jpg');
        $image = Image::createWithFile($this->exampleModel, $file);
        $this->assertTrue(!empty($image->fileSource));
        \Storage::disk(config('imageable.disk'))->delete($path.'original/'.$newName); // Cleanup - delete file
    }

    /** @test */
    public function image_model_is_populated()
    {
        $size = 'original';
        $file = UploadedFile::fake()->image('avatar.jpg', 2000, 2000);
        
        $model = new Image();
        $file->alt_text = 'Alt text test';
        $file->description = 'Image description';
        
        $calculatedPath = "{$this->exampleModel->uploadPath}/{$this->exampleModel->id}/";
        $model->setModel($this->exampleModel);
        $model->setFile($file);
        $model->setProperties();

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
        $file = UploadedFile::fake()->image('avatar.jpg', 2000, 2000)->size(100);
        $configSize = config('imageable.sizes.small');
        $parent = $this->exampleModel;
        $path = config('filesystems.disks.'.config('imageable.disk').'.root');
        $savePath = "{$path}/{$parent->uploadPath}/small/{$file->name}";
        $resizer = new ImageResizer($file);
        $resizedImage = $resizer->reSizeTo($configSize)->saveTo($savePath); // The important line

        $this->assertEquals($configSize['width'],$resizedImage->width());
        $this->assertEquals($configSize['height'],$resizedImage->height());
        $this->assertTrue(\Storage::disk(config('imageable.disk'))->exists("{$parent->uploadPath}/small/{$file->name}"));
        \Storage::disk(config('imageable.disk'))->delete("{$parent->uploadPath}/small/{$file->name}"); // Cleanup - delete file
    }



}

/**
* Parent
*/
class ExModelObject extends Model
{
    public $uploadPath = 'basepath';
}
