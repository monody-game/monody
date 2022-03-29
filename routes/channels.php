<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Redis;

Broadcast::channel('home', function (User $user) {
    return true;
});

Broadcast::channel('game.{gameId}', function (User $user, $gameId) {
    $game = Redis::get("game:{$gameId}");

    $game = json_decode($game, true);

    if (isset($game['users']) && !in_array($user->id, $game['users'], true)) {
        $game['users'][] = $user->id;

        Redis::set('game:' . $gameId, json_encode($game));
    }

    return $user;
});
