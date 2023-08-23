<?php

namespace App\Services;

use App\Enums\GameType;
use App\Facades\Redis;
use App\Models\User;
use App\Traits\GameHelperTrait;

class ListGamesService
{
    use GameHelperTrait;

    public function list(string $type): array
    {
        $games = $this->getGames();
        $list = [];

        if ($games === []) {
            return [];
        }

        foreach ($games as $game) {
            // Ignore everything that is not of "game:[id]" form
            if (!preg_match('/^game:[^:]+$/', $game)) {
                continue;
            }

            $gameData = Redis::get($game);

            // Can't retrieve game data ? Ignore it
            if (!$gameData || !is_array($gameData)) {
                continue;
            }

            // Game is started ? Ignore it
            if ($gameData['is_started']) {
                continue;
            }

            // Game is empty (so to be deleted) ? Ignore it
            if (count($gameData['users']) === 0) {
                continue;
            }

            // The game is private ? Ignore it before user can sneak in and retrieve only private games 👀
            if ($this->isOfType($gameData['type'], GameType::PRIVATE_GAME->value)) {
                continue;
            }

            // Game type is not the one wanted
            if ($type !== '*' && !$this->isOfType($gameData['type'], (int) $type)) {
                continue;
            }

            $owner = User::where('id', $gameData['owner'])->first();

            if (!$owner) {
                continue;
            }

            $gameData['owner'] = [
                'id' => $owner->id,
                'username' => $owner->username,
                'avatar' => $owner->avatar,
            ];

            $gameData['id'] = str_replace('game:', '', $game);
            unset($gameData['assigned_roles']);
            unset($gameData['is_started']);

            $list[] = $gameData;
        }

        return $list;
    }

    /**
     * @return array{}|string[]
     */
    private function getGames(): array
    {
        $cursor = 0;

        return Redis::scan($cursor, ['MATCH' => 'game:*', 'COUNT' => 20])[1];
    }
}
