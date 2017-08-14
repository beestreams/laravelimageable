<?php
use Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UnitTest extends TestCase
{
    use DatabaseMigrations;



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
        $this->exampleModel = new ModelObject();
        $this->exampleModel->id = 1;
    }
}

/**
* Parent
*/
class ModelObject extends Model
{

}