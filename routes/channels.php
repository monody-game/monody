<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Redis;

Broadcast::channel('home', function (User $user) {
    return $user;
});

Broadcast::channel('game.{gameId}', function (User $user, $gameId) {
    $game = Redis::get("game:{$gameId}");

    $game = json_decode($game, true);

    if (isset($game['users'])) {
        return in_array($user->id, $game['users'], true);
    }

    return false;
});
