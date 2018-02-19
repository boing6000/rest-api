<?php
$version = getConfig('apiVersion');

genRoutes($version);


function genRoutes($baseName = '/') {
	Route::group(['prefix' => $baseName], function(){
		
		Route::get('/', 'BaseApiController@debug');
		
		Route::group(['prefix' => 'auth/'], function() {
			$authController = getConfig('authController');
			Route::post('login', "$authController@login");
			Route::delete('logout', "$authController@logout");
			Route::get('ad', "$authController@activeDirectory");
		});
		$routes = getConfig('genericRoutes');
		if(isset($routes) && count($routes) > 0){
			foreach (getConfig('genericRoutes') as $key => $group) {
				parseGroup($group);
			}
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
    
        Route::get("$key", "BaseApiController@index")->name('api.' . $key . '.get');
        Route::get("$key/{id}", "BaseApiController@show")->name('api.' . $key . '.get-id');
        Route::post("$key", "BaseApiController@create")->name('api.' . $key . '.post');
        Route::put("$key/{id}", "BaseApiController@update")->name('api.' . $key . '.put');
        Route::delete("$key/{id}", "BaseApiController@delete")->name('api.' . $key . '.delete');
        Route::get("$key/trash/{id}", "BaseApiController@trash")->name('api.' . $key . '.get-trash');
        Route::get("$key/trash/{id}/restore", "BaseApiController@restoreTrashed")->name('api.' . $key . '.restore-trash');
        Route::delete("$key/trash/{id}/remove", "BaseApiController@removeTrashed")->name('api.' . $key . '.delete-trash');
    }
}