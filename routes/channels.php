<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Redis;

Broadcast::channel('game.{gameId}', function (User $user, $gameId) {
    $game = Redis::get("game:{$gameId}");

    $users = json_decode($game, true)['users'];

    return in_array($user->id, $users);
});
