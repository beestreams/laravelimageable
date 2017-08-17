<?php
use Beestreams\LaravelImageable\Helpers\ImageResizer;
use Beestreams\LaravelImageable\Models\Image;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Beestreams\LaravelImageable\Traits\Imageable;

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
        $image = $this->createOriginal();
        $configSize = config('imageabledd.sizes.small');
        $resizedImage = $image->createResized($image->id, 'small');
        $sourceFile = $resizedImage->sourceFile;
        $this->assertTrue(!empty($sourceFile));
        $resizedImage->delete();
    }



}

/**
* Parent
*/
class ExModelObject extends Model
{
    use Imageable;
    public $uploadPath = 'basepath';
}
