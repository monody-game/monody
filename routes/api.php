<?php

use App\Http\Middleware\OptionalAuthentication;
use App\Http\Middleware\RestrictToLocalNetwork;
use App\Http\Middleware\VerifiedEmailNeeded;
use Illuminate\Support\Facades\Route;

Route::get('/ping', 'PingController@ping');

Route::post('/auth/login', 'Auth\LoginController@login');
Route::post('/auth/password/reset', 'Auth\PasswordController@reset');
Route::post('/auth/password/validate', 'Auth\PasswordController@token');

Route::get('/game/list', 'Game\GameController@list');

Route::get('/oauth/check/discord', 'Oauth\DiscordOauthController@check');
//Route::get('/oauth/check/google', 'Oauth\GoogleOauthController@check');

Route::group(['middleware' => ['auth:api', VerifiedEmailNeeded::class]], function () {
    Route::get('/oauth/link/discord', 'Oauth\DiscordOauthController@link');
    Route::get('/oauth/unlink/discord', 'Oauth\DiscordOauthController@unlink');
    Route::get('/oauth/user/discord', 'Oauth\DiscordOauthController@user');
    //Route::get('/oauth/link/google', 'Oauth\GoogleOauthController@link');
});

Route::get('/roles/', 'RoleController@all');
Route::get('/roles/game/{gameId}', 'RoleController@game');
Route::get('/roles/get/{id}', 'RoleController@get');
Route::get('/roles/{group}', 'RoleController@group');

Route::get('/rounds/{gameId?}', 'RoundController@all');
Route::get('/round/{round}/{gameId?}', 'RoundController@get');

Route::get('/state/{state}', 'StateController@get');
Route::get('/state/{state}/message', 'StateController@message');

Route::get('/teams', 'TeamController@all');
Route::get('/team/{id}', 'TeamController@get');

Route::get('/interactions/actions', 'Game\GameActionsController@all');
Route::get('/interactions/actions/{gameId}/{interactionId}', 'Game\GameActionsController@get');

Route::group(['middleware' => OptionalAuthentication::class], function () {
    Route::get('/stats/{userId?}', 'StatisticsController@index');
    Route::get('/badges/{userId?}', 'BadgeController@get');
});

Route::group(['middleware' => RestrictToLocalNetwork::class], function () {
    Route::post('/auth/register', 'Auth\RegisterController@register');
    Route::post('/roles/assign', 'RoleController@assign');

    Route::delete('/game', 'Game\GameController@delete');
    Route::get('/game/{gameId}', 'Game\GameController@data');
    Route::post('/game/join', 'Game\GameController@join');
    Route::post('/game/leave', 'Game\GameController@leave');

    Route::post('/interactions', 'Game\GameInteractionController@create');
    Route::delete('/interactions', 'Game\GameInteractionController@close');

    Route::post('/game/message/deaths', 'Game\GameChatController@death');
    Route::post('/game/chat/lock', 'Game\GameChatController@lock');

    Route::post('/game/end/check', 'Game\EndGameController@check');
    Route::post('/game/end', 'Game\EndGameController@index');

    Route::get('/user/discord/{discordId}', 'UserController@discord');
    Route::get('/user/discord/{discordId}/share/{theme?}', 'ShareController@discord');
});

Route::group(['middleware' => ['auth:api']], function () {
    Route::post('/interactions/use', 'Game\GameInteractionController@interact');
    Route::post('/interactions/status', 'Game\GameInteractionController@status');

    Route::post('/oauth/unlink/discord', 'Oauth\DiscordOauthController@unlink');
    Route::post('/oauth/unlink/google', 'Oauth\GoogleOauthController@unlink');

    Route::get('/avatars/generate', 'AvatarController@generate');
    Route::post('/avatars', 'AvatarController@upload');
    Route::delete('/avatars', 'AvatarController@delete');

    Route::post('/auth/logout', 'Auth\LoginController@logout');

    Route::get('/user', 'UserController@user')->name('verification.notice');
    Route::patch('/user', 'UserController@update');
    Route::get('/user/share/{theme?}', 'ShareController@index');

    Route::put('/game', 'Game\GameController@new');
    Route::post('/game/check', 'Game\GameController@check');

    Route::get('/game/{id}/users', 'Game\GameUsersController@list');
    Route::get('/game/user/{id}/role', 'Game\GameUsersController@role');

    Route::post('/game/message/send', 'Game\GameChatController@send');

    Route::get('/exp/get', 'ExpController@get');

    Route::get('/email/verify/{id}/{hash}', "Auth\VerifyEmailController@verify")
        ->middleware(['signed'])
        ->name('verification.verify');

    Route::get('/email/notify', 'Auth\VerifyEmailController@notice')
        ->middleware(['throttle:6,1'])
        ->name('verification.send');
});
