<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Facades\Redis;

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

        return json_decode(Redis::get("game:$gameId:members"), true);
    }

    /**
     * @throws Exception more than one user was found for the given id
     *
     * @return array|false returns the user or false if it is not found
     */
    public function getMember(string $userId, string $gameId): array|false
    {
        if (!$this->exists("game:$gameId:members")) {
            return false;
        }

        $members = $this->getMembers($gameId);
        $members = array_filter($members, fn ($member) => $member['user_id'] === $userId);

        if (0 === \count($members)) {
            return false;
        }

        if (\count($members) > 1) {
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
        return Redis::exists($key);
    }
}
