# Package to add Images to models

## Usage
1. Install via composer
2. Add service provider `Beestreams\LaravelImageable\ImageableProvider::class`
3. run `php artisan vendor:publish --tag=config`
4. Migrate database
5. Include Imageable trait on models
6. Set upload path on model `protected $imagePath = 'example/'`
7. When file is available, add it to model `$model->attachImage($file);`

When file is added to model, it first persists an Image model to DB
it then saves the file to specified path.
You can set sizes in config to make several images. These are jobs dispatched to queue.
For each image size it makes an additional model.
When model is deleted, it also deletes the file.
When original model is deleted all related models and files are deleted

For method list see `Imageable` trait or `IntegrationTest`

If you want alt_text and description for your files … support for that is coming

TODO:
- Get file from url (route and response)
- Multiple file types support
- SVG support
- create failsafe in ResizeImage job. If size does not exist, fail gracefully
- Integrationtest is a test-dump. Clean up and refactor
- Fix hacky Job handle method
- Whats up with all the save methods?
- External storage services
- Delete all sizes on delete
- Create controller for image and refactor routes file


## Important
There is no validation in this package, please validate your requests before using.

## Vendor publish
If you need to modify migrations, models or traits you can ´vendor:publish´ this package. (not tested)


