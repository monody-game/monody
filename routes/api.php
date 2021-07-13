<?php

use Illuminate\Support\Facades\Route;

Route::post('/auth/login', 'App\Http\Controllers\Api\AuthController@login')->name('login');
Route::post('/auth/register', 'App\Http\Controllers\Api\AuthController@register');

Route::group(['middleware' => ['auth:api']], function () {
    Route::get('/user', 'App\Http\Controllers\Api\AuthController@user');
    Route::post('/auth/logout', '\App\Http\Controllers\Api\AuthController@logout')->name('auth.logout');
    Route::get('/game/token', '\App\Http\Controllers\Api\GameController@token');
});


