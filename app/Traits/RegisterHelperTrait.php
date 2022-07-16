<?php

namespace App\Traits;

use App\Models\User;

trait RegisterHelperTrait
{
    public function setCurrentUserGameActivity(string $userId, string $gameId): void
    {
        $user = User::find($userId);
        $user->current_game = $gameId;
        $user->save();
    }

    public function getCurrentUserGameActivity(string $userId): string
    {
        $user = User::find($userId);

        return $user->current_game;
    }
}
