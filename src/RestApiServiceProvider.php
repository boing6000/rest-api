<?php

namespace Boing\RestApi;

use Illuminate\Support\ServiceProvider;

class RestApiServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/boing-rest-api.php' => config_path(
                'boing-rest-api.php'
            )
        ]);
        
		/*if (! $this->app->routesAreCached()) {
            require __DIR__.'/routes.php';
        }*/
	
		
		$this->app['router']->group(['namespace' => 'Boing\RestApi\Controllers'], function () {
            require __DIR__.'/routes.php';
        });
    }
    public function register()
    {
    }
}