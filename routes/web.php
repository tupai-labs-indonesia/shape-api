<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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

$router->get('/', function () use ($router) {
    return $router->app->version();
});


$router->group(['prefix' => 'api'], function () use ($router) {
    $router->post('login', 'AuthController@login');
    $router->get('users', 'UserController@getUsers');
    $router->post('user/registration', 'UserController@create');
    $router->get('assets/search', 'AssetController@search');
    $router->get('assets/filter', 'AssetController@filter');
    $router->get('assets', 'AssetController@getAssets');
    $router->post('asset/add', 'AssetController@create');
    $router->get('asset/{id}', 'AssetController@getAssetById');
    $router->get('asset/dropdown/{id}', 'AssetController@getAssetDropdown');
    $router->group(['middleware' => 'auth'], function () use ($router){
        $router->post('logout', 'AuthController@logout');
        $router->post('user/update', 'UserController@update');
    });
});

