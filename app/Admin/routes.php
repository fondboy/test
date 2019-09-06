<?php

use Illuminate\Routing\Router;
Admin::registerAuthRoutes();

//Route::get('/admin/network/getNetworkList', 'NetworkController@getNetworkList');
Route::get('/admin/task/test', 'NetworkController@getNetworkList')->name('taskTest');
//var_dump( config('admin.route.prefix'), config('admin.route.namespace'), config('admin.route.middleware'));
//exit;


Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {
	
	$namespace 	= config('admin.route.namespace');
    $router->get('/', 'HomeController@index');
	$router->resource('admin', HomeController::class);
	$router->resource('users', UserController::class);
	$router->resource('network', NetworkController::class);
	$router->resource('distr', DistributeController::class);
	$router->resource('mall', MallBinDingController::class);
	$router->resource('site', SiteController::class);
	$router->resource('device', DeviceController::class);
// 	$router->resource('task', TaskController::class);

	// 以下只是提供个demo
	$router->any('{controller}/{action}',function($controller,$action) use ($namespace) {
		$class 	= $namespace.'\\'.ucfirst($controller).'Controller';
		return (new $class)->$action();
	});
	
});





