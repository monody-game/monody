<?php

use Illuminate\Http\Request;
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

Route::post('/login', 'App\Http\Controllers\AuthController@login')->name('auth.login');
Route::post('/register', 'App\Http\Controllers\AuthController@register')->name('auth.register');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['auth:api', 'api.']], function () {
    Route::post('/logout', '\App\Http\Controllers\AuthController@logout')->name('auth.logout');
    Route::post('/game/{id}/chat/send', '\App\Http\Controllers\ChatController@send')->name('chat.send');
    Route::get('/game/{id}/chat/all', '\App\Http\Controllers\ChatController@all')->name('chat.get');
});


