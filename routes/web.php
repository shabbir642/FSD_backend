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
    $router->get('countuser','ManageController@countuser');
    $router->post('login','UserController@login');
    $router->get('list','ManageController@userlist');
    $router->get('filteruser/{keyword}','ManageController@filteruser');
    $router->get('me','ManageController@me');
    $router->get('logout','ManageController@logout');
    $router->get('refresh','ManageController@refresh');
    $router->delete('delete/{id}','ManageController@delete');
    $router->post('makeadmin','ManageController@makeadmin');
    $router->post('removeadmin','ManageController@removeadmin');
    $router->post('adduser','ManageController@adduser');
    $router->post('forgotpassword','UserController@forgotpassword');
    $router->post('resetpassword','UserController@resetpassword');

    $router->post('createtask','TaskController@createtask');
    $router->get('viewtask/{id}','TaskController@viewtask');
    $router->post('updatestatus','TaskController@updatestatus');
    $router->post('updatetask','TaskController@updatetask');
    $router->get('deletetask/{id}','TaskController@deletetask');
    $router->post('mytask','TaskController@mytask');
    $router->post('tasktome','TaskController@taskstome');
    $router->post('filtertask','TaskController@filtertask');
    $router->get('listask','TaskController@listtask');
    $router->get('taskfortoday/{today}','TaskController@taskfortoday');
    $router->get('counttask',['middleware' => 'Admin', 'uses' => 'TaskController@counttask']);
 });
