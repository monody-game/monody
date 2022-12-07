<?php

namespace App\Traits;

use App\Enums\Roles;
use App\Enums\Teams;
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
        return Redis::exists($key);
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

    /**
     * @return array<int|string>|false
     */
    public function getUserIdByRole(Roles $role, string $gameId): array|false
    {
        $game = Redis::get("game:$gameId");

        if (!$game['is_started']) {
            return false;
        }

        return array_keys($game['assigned_roles'], $role->value, true);
    }

    public function getRoleByUserId(string $userId, string $gameId): Roles|false
    {
        $game = Redis::get("game:$gameId");

        if (!$game['is_started']) {
            return false;
        }

        return Roles::from($game['assigned_roles'][$userId]);
    }

    public function getUsersByTeam(Teams $team, string $gameId): array
    {
        $roles = $team->roles();
        $members = [];

        foreach ($roles as $role) {
            $members[] = $this->getUserIdByRole($role, $gameId);
        }

        if ($members[0] === false) {
            return [];
        }

        return $members[0];
    }

    public function kill(string $userId, string $gameId, string $context): bool
    {
        $member = $this->getMember($userId, $gameId);
        $members = $this->getMembers($gameId);
        $index = array_search($member, $members, true);
        if (!$member || false === $index) {
            return false;
        }

        $member = array_splice($members, (int) $index, 1)[0];

        $member['user_info']['is_dead'] = true;
        $members = [...$members, $member];

        $deaths = Redis::get("game:$gameId:deaths") ?? [];
        Redis::set("game:$gameId:members", $members);

        Redis::set("game:$gameId:deaths", [...$deaths, [
            'user' => $userId,
            'context' => $context,
        ]]);

        return true;
    }

    public function isWerewolf(string $userId, string $gameId): bool
    {
        return in_array($userId, $this->getUsersByTeam(Teams::Werewolves, $gameId), true);
    }
}
