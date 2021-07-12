<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

/*$router->get('/', function () use ($router) {
    return $router->app->version();
});*/

// Home
$router->get('/', [
	'as' => 'home',
	'uses' => 'MainController@index',
]);

// Selección
$router->post('/load', [
	'as' => 'load',
	'uses' => 'MainController@load',
]);

// Import
$router->get('/import/' . config('app.import-slug'), [
	'as' => 'import',
	'uses' => 'ImportController@import',
]);
$router->get('/import/init', [
	'as' => 'import-init',
	'uses' => 'ImportController@init',
]);
$router->get('/import/lists', [
	'as' => 'import-lists',
	'uses' => 'ImportController@loadLists',
]);
$router->get('/import/update_increments', [
	'as' => 'import-update',
	'uses' => 'ImportController@updateIncrements',
]);
// Restaurar un día perdido (parámetro date)
$router->get('/import/restore', [
	'as' => 'import-restore',
	'uses' => 'ImportController@restore',
]);
