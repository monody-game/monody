<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('login', 'App\Http\Controllers\Api\AuthController@login')->name('login');
Route::post('register', 'App\Http\Controllers\Api\AuthController@register');

Route::middleware('auth:api')->get('/user', 'App\Http\Controllers\Api\AuthController@user');

Route::group(['middleware' => ['auth:api']], function () {
    Route::post('/logout', '\App\Http\Controllers\Api\AuthController@logout')->name('auth.logout');
});


