<?php

use App\Http\Middleware\OptionalAuthentication;
use App\Http\Middleware\RestrictToLocalNetwork;
use App\Http\Responses\JsonApiResponse;
use Illuminate\Support\Facades\Route;

Route::get('/ping', 'PingController@ping');

Route::post('/auth/register', 'Auth\RegisterController@register');
Route::post('/auth/login', 'Auth\LoginController@login');
Route::post('/auth/password/reset', 'Auth\PasswordController@reset');
Route::post('/auth/password/validate', 'Auth\PasswordController@token');

Route::get('/game/list/{type?}', 'Game\GameListController@list');

Route::get('/roles', 'RoleController@all');
Route::get('/roles/list', 'RoleController@list');
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

Route::get('/leaderboard/{leaderboard}', 'LeaderboardController@index');

Route::group(['middleware' => OptionalAuthentication::class], function () {
    Route::get('/stats/{userId?}', 'StatisticsController@show');

    Route::get('/badges/{userId?}', 'BadgeController@get');
});

Route::group(['middleware' => ['auth:api']], function () {
    Route::get('/oauth/check/discord', 'Oauth\DiscordOauthController@check');
    Route::get('/oauth/link/discord', 'Oauth\DiscordOauthController@link');
    Route::get('/oauth/unlink/discord', 'Oauth\DiscordOauthController@unlink');
    Route::get('/oauth/user/discord', 'Oauth\DiscordOauthController@user');

    //Route::get('/oauth/check/google', 'Oauth\GoogleOauthController@check');
    //Route::get('/oauth/link/google', 'Oauth\GoogleOauthController@link');

    Route::post('/interactions/use', 'Game\GameInteractionController@interact');

    Route::post('/oauth/unlink/discord', 'Oauth\DiscordOauthController@unlink');
    Route::post('/oauth/unlink/google', 'Oauth\GoogleOauthController@unlink');

    Route::post('/avatars', 'AvatarController@upload');
    Route::delete('/avatars', 'AvatarController@delete');

    Route::post('/auth/logged', function () {
        return JsonApiResponse::make()->withoutCache();
    });
    Route::post('/auth/logout', 'Auth\LogoutController@index');
    Route::post('/auth/logout/all', 'Auth\LogoutController@all');

    Route::get('/user', 'UserController@user')->name('verification.notice');
    Route::patch('/user', 'UserController@update');
    Route::get('/user/share/{theme?}', 'ShareProfileController@index');

    Route::put('/game', 'Game\GameController@new');
    Route::post('/game/check', 'Game\GameController@check');
    Route::get('/game/share', 'Game\GameSharingController@index');

    Route::get('/game/{id}/users', 'Game\GameUsersController@list');
    Route::get('/game/{id}/discord', 'Game\GameController@discord');
    Route::get('/game/{gameId}/user/{id}/role', 'Game\GameUsersController@role');

    Route::post('/game/message/send', 'Game\GameChatController@send');

    Route::get('/exp', 'ExpController@get');

    Route::get('/email/verify/{id}/{hash}', "Auth\VerifyEmailController@verify")
        ->middleware(['signed'])
        ->name('verification.verify');

    Route::get('/email/notify', 'Auth\VerifyEmailController@notice')
        ->middleware(['throttle:6,1'])
        ->name('verification.send');
});

Route::group(['middleware' => RestrictToLocalNetwork::class], function () {
    Route::post('/roles/assign', 'RoleController@assign');

    Route::delete('/game', 'Game\GameController@delete');
    Route::post('/game/join', 'Game\JoinGameController@join');
    Route::post('/game/leave', 'Game\JoinGameController@leave');

    Route::get('/game/data/{gameId}/{userId}', 'Game\GameDataController@data');

    Route::post('/interactions', 'Game\GameInteractionController@create');
    Route::delete('/interactions', 'Game\GameInteractionController@close');
    Route::post('/interactions/status', 'Game\GameInteractionController@status');

    Route::post('/game/message/deaths', 'Game\GameChatController@death');
    Route::post('/game/chat/lock/{lock}', 'Game\GameChatController@lock');

    Route::post('/game/start/check', 'Game\StartGameController@check');
    Route::post('/game/end/check', 'Game\EndGameController@check');
    Route::post('/game/end', 'Game\EndGameController@index');

    Route::post('/game/vocal/joined', 'Game\GameUsersController@joined');
    Route::post('/game/kill', 'Game\GameUsersController@eliminate');

    Route::get('/user/discord/{discordId}', 'UserController@discord');
    Route::get('/user/discord/{discordId}/share/{theme?}', 'ShareProfileController@discord');
});
