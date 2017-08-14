# Package to add Category functionality to models

## Usage
1. Install via composer
2. Add service provider `Beestreams\LaravelImageable\ImageableProvider::class`
3. Migrate database
4. Include Imageable trait on models
5. Set upload path on model `protected $path = 'example'`
6. When file is available, add it to model `$model->addImage($file);`

When file is added to model, it first persists an Image model to DB
it thensaves the file to specified path.
You can set alternative sizes to make several images. These are jobs dispatched to queue.
For each image size it makes an additional model.
When model is deleted, it also deletes the file.

For method list see `Categorizable` trait or `IntegrationTest`

## Important
There is no validation in this package, please validate your requests before using.

## Vendor publish
If you need to modify migrations, models or traits you can ´vendor:publish´ this package. (not tested)


