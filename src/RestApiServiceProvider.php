<?php

namespace Boing\RestApi;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class RestApiServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/boing-rest-api.php' => config_path(
                'boing-rest-api.php'
            )
        ]);
		
		Route::prefix('api')
             ->middleware('api')
             ->namespace('Boing\RestApi\Controllers')
             ->group(__DIR__.'/routes.php');
    }
    public function register()
    {
    }
}