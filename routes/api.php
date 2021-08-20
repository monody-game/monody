<?php

use Illuminate\Support\Facades\Route;

Route::post('/auth/login', 'Api\AuthController@login')->name('login');
Route::post('/auth/register', 'Api\AuthController@register');
Route::get('/oauth/link/discord', 'Api\OauthController@discord');
Route::get('/oauth/link/google', 'Api\OauthController@google');

Route::group(['middleware' => ['auth:api']], function () {
    Route::get('/user', 'Api\AuthController@user');
    Route::post('/auth/logout', 'Api\AuthController@logout')->name('auth.logout');

    Route::get('/game/token', 'Api\GameController@token');
    Route::get('/game/list', 'Api\GameController@list');
    Route::post('/game/new', 'Api\GameController@new');
    Route::post('/game/delete', 'Api\GameController@delete');
});
