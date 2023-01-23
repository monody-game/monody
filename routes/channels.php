<?php

use App\Facades\Redis;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('home', function () {
    return true;
});

Broadcast::channel('game.{gameId}', function (User $user, $gameId) {
    $game = Redis::get("game:$gameId");

    if ($game['is_started']) {
        return false;
    }

    $members = Redis::get("game:$gameId:members") ?? [];
    $member = array_filter($members, fn ($member) => $member['user_id'] === $user->id);

    if ($member) {
        return false;
    }

    if (isset($game['users']) && !in_array($user->id, $game['users'], true)) {
        $game['users'][] = $user->id;

        Redis::set('game:' . $gameId, $game);
    }

    return [
        'id' => $user->id,
        'username' => $user->username,
        'avatar' => $user->avatar,
        'level' => $user->level,
    ];
});
