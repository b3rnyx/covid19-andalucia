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

// Changelog
$router->get('/changelog', [
	'as' => 'changelog',
	'uses' => 'MainController@changelog',
]);

// IMPORT

// Import
$router->get('/import/' . config('app.import-slug'), [
	'as' => 'import',
	'uses' => 'ImportController@import',
]);
// Import datos ministerio de ocupación hospitalaria
$router->get('/import/' . config('app.import-slug') . '/hospitals', [
	'as' => 'import-hospital',
	'uses' => 'ImportController@importHospitals',
]);

// INIT

// Inicializa las tablas de datos
$router->get('/import/' . config('app.import-slug') . '/init', [
	'as' => 'import-init',
	'uses' => 'ImportController@init',
]);
// Inicializa los datos de ocupación hospitalaria
$router->get('/import/' . config('app.import-slug') . '/init_hospitals', [
	'as' => 'import-init-hospitals',
	'uses' => 'ImportController@initHospitals',
]);
// Inicializa las listas
$router->get('/import/' . config('app.import-slug') . '/lists', [
	'as' => 'import-lists',
	'uses' => 'ImportController@loadLists',
]);

// TOOLS

// Actualiza los índices de incremento
$router->get('/import/' . config('app.import-slug') . '/update_increments', [
	'as' => 'import-update',
	'uses' => 'ImportController@updateIncrements',
]);
// Restaurar un día perdido (parámetro date)
$router->get('/import/' . config('app.import-slug') . '/restore', [
	'as' => 'import-restore',
	'uses' => 'ImportController@restore',
]);
// Borra los duplicados
$router->get('/import/' . config('app.import-slug') . '/delete_duplicated', [
	'as' => 'import-delete-duplicated',
	'uses' => 'ImportController@deleteDuplicated',
]);
