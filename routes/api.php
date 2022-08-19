<?php

use App\Http\Middleware\RestrictToDockerNetwork;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

Route::get('/ping', function () {
    return new JsonResponse(['Alive'], Response::HTTP_OK);
});

Route::post('/auth/login', 'Auth\LoginController@login');
Route::post('/auth/register', 'Auth\RegisterController@register');

Route::get('/oauth/link/discord', 'Oauth\DiscordOauthController@link');
Route::get('/oauth/link/google', 'Oauth\GoogleOauthController@link');
Route::get('/oauth/check/discord', 'Oauth\DiscordOauthController@check');
Route::get('/oauth/check/google', 'Oauth\GoogleOauthController@check');

Route::get('/roles', 'RoleController@all');
Route::get('/roles/get/{id}', 'RoleController@get');
Route::get('/roles/{group}', 'RoleController@group');

Route::group(['middleware' => RestrictToDockerNetwork::class], function () {
    Route::post('/game/join', 'Game\GameController@join');
    Route::post('/game/leave', 'Game\GameController@leave');
    Route::post('/game/aftervote', 'Game\GameVoteController@afterVote');
});

Route::group(['middleware' => ['auth:api']], function () {
    Route::post('/oauth/unlink/discord', 'Oauth\DiscordOauthController@unlink');
    Route::post('/oauth/unlink/google', 'Oauth\GoogleOauthController@unlink');

    Route::get('/avatars/generate', 'AvatarController@generate');
    Route::put('/avatars', 'AvatarController@upload');
    Route::delete('/avatars', 'AvatarController@delete');

    Route::post('/auth/logout', 'Auth\LoginController@logout');

    Route::get('/user', 'UserController@user');
    Route::patch('/user', 'UserController@update');

    Route::get('/game/list', 'Game\GameController@list');
    Route::post('/game/new', 'Game\GameController@new');
    Route::post('/game/delete', 'Game\GameController@delete');
    Route::post('/game/check', 'Game\GameController@check');

    Route::get('/game/users', 'Game\GameUsersController@list');
    Route::get('/game/user/{id}/role', 'Game\GameUsersController@role');

    Route::post('/game/message/send', 'Game\GameMessageController@send');
    Route::post('/game/vote', 'Game\GameVoteController@vote');

    Route::get('/teams', 'TeamController@all');

    Route::get('/exp/get', 'ExpController@get');
});
