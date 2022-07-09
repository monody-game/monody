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
        return json_decode(Redis::get("game:$gameId:members"), true);
    }

    /**
     * @throws Exception more than one user was found for the given id
     *
     * @return array|false returns the user or false if it is not found
     */
    public function getMember(string $userId, string $gameId): array|false
    {
        $members = $this->getMembers($gameId);
        $members = array_filter($members, fn ($member) => $member['user_id'] === $userId);

        if (0 === \count($members)) {
            return false;
        }

        if (\count($members) > 1) {
            throw new Exception("More than one user was found for the given id : $userId");
        }

        return $members[0];
    }

    public function hasMember(string $userId, string $gameId): bool
    {
        return (bool) $this->getMember($userId, $gameId);
    }
}
