<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', 'CardJsonController@index');

Route::get('/users', 'UserController@index');
//Route::post('/users', 'UserController@post');
Route::post('/users', function() {
    return 'asdfasdf';
});

//Route::post('/login', 'UserController@login');
Route::post('/login', function() {
    dd('asdf');
});
