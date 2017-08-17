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
        $this->loadMigrationsFrom(__DIR__.'/Migrations');

        $this->loadRoutesFrom(__DIR__.'/routes.php');
        
        $this->publishes([
            __DIR__ . '/Migrations' => $this->app->databasePath() . '/migrations'
        ], 'migrations');

        $this->publishes([
            __DIR__ . '/Traits' => $this->app->basePath() . '/Morphable'
        ], 'traits');
        
        $this->publishes([
            __DIR__.'/config/imageable.php' => config_path('imageable.php'),
        ], 'config');
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
