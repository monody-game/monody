<?php

namespace App\Services;

use App\Facades\Redis;
use App\Models\User;

class ListGamesService
{
    public function list(string $type, bool $isFromLocalNetwork): array
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

            if ($type !== '*' && !$this->areTypeEquals($gameData['type'], (int) $type)) {
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

    private function areTypeEquals(int $firstType, int $secondType): bool
    {
        return ($firstType & $secondType) === $secondType;
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
