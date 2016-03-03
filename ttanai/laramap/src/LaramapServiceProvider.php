<?php namespace Ttanai\Laramap;

use App;
use Illuminate\Support\ServiceProvider;

class LaramapServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        App::bind('laramap', function()
        {
            return new Laramap;
        });
    }

}
