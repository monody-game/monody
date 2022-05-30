<?php

use Illuminate\Support\Facades\Route;

Route::post('/auth/login', 'Api\AuthController@login');
Route::post('/auth/register', 'Api\AuthController@register');
Route::get('/oauth/discord/link', 'Api\OauthController@discord');
Route::get('/oauth/google/link', 'Api\OauthController@google');

Route::group(['middleware' => ['auth:api']], function () {
    Route::get('/avatar/{path}', 'Api\AvatarController@show');
    Route::get('/avatars/generate', 'Api\AvatarController@generate');

    Route::post('/auth/logout', 'Api\AuthController@logout');

    Route::get('/user', 'Api\AuthController@user');
    Route::get('/user/avatar', 'Api\UserController@avatar');

    Route::get('/game/list', 'Api\GameController@list');
    Route::post('/game/new', 'Api\GameController@new');
    Route::post('/game/delete', 'Api\GameController@delete');
    Route::post('/game/check', 'Api\GameController@check');

    Route::get('/game/users', 'Api\GameUsersController@list');
    Route::post('/game/message/send', 'Api\GameMessageController@send');

    Route::get('/roles', 'Api\RoleController@all');
    Route::get('/roles/get/{id}', 'Api\RoleController@get');

    Route::get('/teams', 'Api\TeamController@all');

    Route::get('/exp/get', 'Api\ExpController@get');
});
