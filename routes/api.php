<?php

use Illuminate\Support\Facades\Route;

Route::post('/auth/login', 'Auth\LoginController@login');
Route::post('/auth/register', 'Auth\RegisterController@register');

Route::get('/oauth/discord/link', 'OauthController@discordLink');
Route::get('/oauth/google/link', 'OauthController@googleLink');
Route::get('/oauth/discord/check', 'OauthController@discordCheck');
Route::get('/oauth/google/check', 'OauthController@googleCheck');

Route::group(['middleware' => ['auth:api']], function () {
    Route::get('/avatar/{path}', 'AvatarController@show');
    Route::get('/avatars/generate', 'AvatarController@generate');

    Route::post('/auth/logout', 'Auth\LoginController@logout');

    Route::get('/user', 'UserController@user');
    Route::get('/user/avatar', 'UserController@avatar');

    Route::get('/game/list', 'GameController@list');
    Route::post('/game/new', 'GameController@new');
    Route::post('/game/delete', 'GameController@delete');
    Route::post('/game/check', 'GameController@check');

    Route::get('/game/users', 'GameUsersController@list');
    Route::post('/game/message/send', 'GameMessageController@send');

    Route::get('/roles', 'RoleController@all');
    Route::get('/roles/get/{id}', 'RoleController@get');

    Route::get('/teams', 'TeamController@all');

    Route::get('/exp/get', 'ExpController@get');
});
