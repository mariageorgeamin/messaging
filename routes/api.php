<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['namespace' => 'API'], function () {

    Route::post('register', 'AuthController@register')->name('register');
    Route::post('login', 'AuthController@login')->name('login');

    Route::group(['middleware' => ['auth:api','json.response']], function () {
        Route::group(['prefix' => 'message'], function () {
            Route::get('/all','MessagesController@index');
            Route::post('/create','MessagesController@store');
            Route::put('/update/{id}','MessagesController@update');
            Route::get('/show/{id}','MessagesController@show');
            Route::get('/search','MessagesController@search');
        });
    });
});

