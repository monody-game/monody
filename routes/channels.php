<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('home', function () {
    return true;
});

Broadcast::channel('game.{gameId}', function (User $user, $gameId) {
    $game = $this->redis()->get("game:$gameId");
    $members = $this->redis()->get("game:$gameId:members") ?? [];
    $member = array_filter($members, fn ($member) => $member['user_id'] === $user->id);

    if ($game === null || ($game['is_started'] && count($member) >= 1)) {
        return false;
    }

    if ($member) {
        return false;
    }

    if (isset($game['users']) && !in_array($user->id, $game['users'], true)) {
        $game['users'][] = $user->id;

        $this->redis()->set('game:' . $gameId, $game);
    }

    return [
        'id' => $user->id,
        'username' => $user->username,
        'avatar' => $user->avatar,
        'level' => $user->level,
    ];
});

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (string) $user->id === (string) $id;
});
