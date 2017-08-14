<?php

namespace Beestreams\LaravelImageable; 

use Illuminate\Support\ServiceProvider;

class ImageableProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        require './vendor/autoload.php';
        $this->publishes([
            __DIR__.'/config/imageable.php' => config_path('imageable.php'),
        ], 'config');

        $this->loadMigrationsFrom(__DIR__.'/Migrations');
        
        $this->publishes([
            __DIR__ . '/Migrations' => $this->app->databasePath() . '/migrations'
        ], 'migrations');

        $this->publishes([
            __DIR__ . '/Traits' => $this->app->basePath() . '/Morphable'
        ], 'traits');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
