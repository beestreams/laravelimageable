<?php
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
    public function file_is_saved_to_disk_as_original()
    {
        $newName = 'noavatar.jpg';
        $file = UploadedFile::fake()->image('avatar.jpg');
        $image = new Image();
        $path = 'uploads/1/';
        $image->path = $path;
        $image->name = $newName; 
        $image->saveFile($file);
        $this->assertTrue(\Storage::disk(config('imageable.disk'))->exists($path.'original/'.$newName));
        \Storage::disk(config('imageable.disk'))->delete($path.'original/'.$newName);
        $this->assertFalse(\Storage::disk(config('imageable.disk'))->exists($path.'original/'.$newName));
    }

    /** @test */
    public function image_model_is_populated()
    {
        $size = 'original';
        $file = UploadedFile::fake()->image('avatar.jpg');
        $model = new Image();
        $model->setProperties($file);
        $this->assertEquals($file->size, $model->size);
        $this->assertEquals($file->name, $model->name);
    }
}

/**
* Parent
*/
class ExModelObject extends Model
{
}