<?php

namespace Paulohsilvestre\GeneratorForLaravel;

use Illuminate\Support\ServiceProvider;

class GenerationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        include __DIR__.'/routes/web.php';
        $this->app->make('Paulohsilvestre\GeneratorForLaravel\Controllers\GenerationController');
        $this->loadViewsFrom(__DIR__.'/views', 'generation');
    }
}
