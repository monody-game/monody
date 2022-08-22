<?php

namespace App\Traits;

use App\Facades\Redis;
use function array_key_exists;
use function count;
use Exception;

trait MemberHelperTrait
{
    /**
     * Return json parsed members of the given game.
     */
    public function getMembers(string $gameId): array
    {
        if (!$this->exists("game:$gameId:members")) {
            return [];
        }

        return Redis::get("game:$gameId:members");
    }

    /**
     * @return array|false returns the user or false if it is not found
     *
     * @throws Exception more than one user was found for the given id
     */
    public function getMember(string $userId, string $gameId): array|false
    {
        if (!$this->exists("game:$gameId:members")) {
            return false;
        }

        $members = $this->getMembers($gameId);
        $members = array_filter($members, fn ($member) => $member['user_id'] === $userId);

        if (0 === count($members)) {
            return false;
        }

        if (count($members) > 1) {
            throw new Exception("More than one user was found for the given id : $userId");
        }

        return $members[array_key_first($members)];
    }

    public function hasMember(string $userId, string $gameId): bool
    {
        if (!$this->exists("game:$gameId:members")) {
            return false;
        }

        return (bool) $this->getMember($userId, $gameId);
    }

    public function exists(string $key): bool
    {
        return (bool) Redis::exists($key);
    }

    public function alive(string $userId, string $gameId): bool
    {
        $member = $this->getMember($userId, $gameId);

        if (!$member) {
            return false;
        }

        if (
            array_key_exists('is_dead', $member['user_info']) &&
            true === $member['user_info']['is_dead']
        ) {
            return false;
        }

        return true;
    }
}
