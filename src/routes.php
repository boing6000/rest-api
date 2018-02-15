<?php
$version = getConfig('apiVersion');

genRoutes("api/$version");


function genRoutes($baseName = '/') {
	Route::group(['prefix' => $baseName], function(){
		
		Route::get('/', function(){
			//dd(Route::getRoutes());
			//dd(\Boing\Api\Controllers\BaseApiController::class);
		});
		
		Route::group(['prefix' => 'auth/'], function() {
			$authController = getConfig('authController');
			Route::post('login', "$authController@login");
			Route::delete('logout', "$authController@logout");
			Route::get('ad', "$authController@activeDirectory");
		});
			
		foreach (getConfig('genericRoutes') as $key => $group) {
			parseGroup($group);
		}
	});
}

function getConfig($var){
	return config("boing-rest-api.$var");
}

function parseGroup($group) {
    Route::group($group['options'], function() use($group){
        parseRoutes($group['models']);
    });
}

function parseRoutes($models) {
    foreach ($models as $key => $value) {
        if($key === 'group') {
            parseGroup($value);
        }
		
		//dd('here');
    
        Route::get("$key", "BaseApiController@index")->name($key);
        Route::get("$key/{id}", "BaseApiController@show")->name($key);
        Route::post("$key", "BaseApiController@create")->name($key);
        Route::put("$key/{id}", "BaseApiController@update")->name($key);
        Route::delete("$key/{id}", "BaseApiController@delete")->name($key);
        Route::get("$key/trash/{id}", "BaseApiController@trash")->name($key);
        Route::get("$key/trash/{id}/restore", "BaseApiController@restoreTrashed")->name($key);
        Route::get("$key/trash/{id}/remove", "BaseApiController@removeTrashed")->name($key);
    }
}