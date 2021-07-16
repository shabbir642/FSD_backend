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

// $router->get('/', function () use ($router) {
//     return $router->app->version();
//     // echo ("Hey there");
// });

$router->group(['prefix' => 'api'], function() use($router){
    $router->post('register','UserController@register');
    $router->get('verify/{token}','UserController@get_verified');
    $router->post('login','UserController@login');
    $router->get('list','ManageController@userlist');
    $router->get('me','ManageController@me');
    $router->get('refresh','ManageController@refresh');
    $router->post('delete','ManageController@delete');
    $router->post('makeadmin','ManageController@makeadmin');
    $router->post('removeadmin','ManageController@removeadmin');
    $router->post('adduser','ManageController@adduser');
    $router->get('passrequest/{token}','UserController@passrequest');
    $router->post('resetpassword','UserController@resetpassword');
});
