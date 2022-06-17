<?php

use Illuminate\Support\Facades\Route;

Route::post('/auth/login', 'Auth\LoginController@login');
Route::post('/auth/register', 'Auth\RegisterController@register');

Route::get('/oauth/link/discord', 'Oauth\DiscordOauthController@link');
Route::get('/oauth/link/google', 'Oauth\GoogleOauthController@link');
Route::get('/oauth/check/discord', 'Oauth\DiscordOauthController@check');
Route::get('/oauth/check/google', 'Oauth\GoogleOauthController@check');

Route::group(['middleware' => ['auth:api']], function () {
	Route::post('/oauth/unlink/discord', 'Oauth\DiscordOauthController@unlink');
	Route::post('/oauth/unlink/google', 'Oauth\GoogleOauthController@unlink');

    Route::get('/avatars/generate', 'AvatarController@generate');
    Route::put('/avatars', 'AvatarController@upload');
    Route::delete('/avatars', 'AvatarController@delete');

    Route::post('/auth/logout', 'Auth\LoginController@logout');

    Route::get('/user', 'UserController@user');
	Route::patch('/user', 'UserController@update');
    Route::get('/user/avatar', 'UserController@avatar');

    Route::get('/game/list', 'Game\GameController@list');
    Route::post('/game/new', 'Game\GameController@new');
    Route::post('/game/delete', 'Game\GameController@delete');
    Route::post('/game/check', 'Game\GameController@check');

    Route::get('/game/users', 'Game\GameUsersController@list');
    Route::post('/game/message/send', 'Game\GameMessageController@send');
    Route::post('/game/vote', 'Game\GameVoteController@vote');

    Route::get('/roles', 'RoleController@all');
    Route::get('/roles/get/{id}', 'RoleController@get');

    Route::get('/teams', 'TeamController@all');

    Route::get('/exp/get', 'ExpController@get');
});
