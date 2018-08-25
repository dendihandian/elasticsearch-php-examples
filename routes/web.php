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

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api', 'middleware' => 'cors'], function ($router) {
    $router->group(['prefix' => 'products'], function ($router) {
        $router->get('/', 'ProductController@index');
        $router->post('/', ['middleware' => 'validateProduct', 'uses' => 'ProductController@store']);

        $router->group(['prefix' => '/{id}'], function ($router) {
            $router->get('/', ['middleware' => 'elasticFindProduct', 'uses' => 'ProductController@show']);
            $router->patch('/', ['middleware' => ['validateProduct', 'findProduct'], 'uses' => 'ProductController@update']);
            $router->delete('/', ['middleware' => 'findProduct', 'uses' => 'ProductController@destroy']);
        });

        $router->group(['prefix' => 'search'], function ($router) {
            $router->get('/{query}', 'ProductController@search');
        });

        $router->group(['prefix' => 'suggestion'], function ($router) {
            $router->get('/{query}', 'ProductController@suggestion');
        });

        $router->get('aggregations', 'ProductController@aggs');
    });

    $router->group(['prefix' => 'contacts', 'middleware' => 'jwt.auth'], function ($router) {
        $router->get('/', 'ContactController@index');
        $router->post('/', 'ContactController@store');

        $router->group(['prefix' => '/{id}', 'middleware' => 'findContact'], function ($router) {
            $router->get('/', 'ContactController@show');
            $router->patch('/', 'ContactController@update');
            $router->delete('/', 'ContactController@destroy');
        });

        $router->group(['prefix' => 'search'], function ($router) {
            $router->get('/{query}', 'ContactController@search');
        });

        $router->group(['prefix' => 'suggestion'], function ($router) {
            $router->get('/{query}', 'ContactController@suggestion');
        });
    });

    $router->group(['prefix' => 'auth'], function ($router) {
        $router->post('login', 'AuthController@login');
        $router->post('register', 'AuthController@register');
        $router->post('logout', ['middleware' => 'jwt.auth', 'uses' => 'AuthController@logout']);
        $router->get('user', ['middleware' => 'jwt.auth', 'uses' => 'AuthController@authenticatedUser']);
    });
});
