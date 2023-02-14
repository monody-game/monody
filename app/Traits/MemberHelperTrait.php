<?php

namespace App\Traits;

use App\Enums\Roles;
use App\Enums\States;
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
     * @return array<int|string>
     */
    public function getUserIdByRole(Roles $role, string $gameId): array
    {
        $game = Redis::get("game:$gameId");

        $deaths = Redis::get("game:$gameId:deaths") ?? [];
        $ids = array_keys($game['assigned_roles'], $role->value, true);
        $users = [];

        foreach ($ids as $user) {
            if ($this->alive($user, $gameId) || array_filter($deaths, fn ($death) => $death['user'] === $user) !== []) {
                $users[] = $user;
            }
        }

        return $users;
    }

    public function getRoleByUserId(string $userId, string $gameId): Roles
    {
        $game = Redis::get("game:$gameId");

        return Roles::from($game['assigned_roles'][$userId]);
    }

    public function getUsersByTeam(Teams $team, string $gameId): array
    {
        $roles = $team->roles();
        $members = [];

        foreach ($roles as $role) {
            $member = $this->getUserIdByRole($role, $gameId);

            if ($member) {
                $members = array_merge($member, $members);
            }
        }

        return $members;
    }

    public function kill(string $userId, string $gameId, string $context): bool
    {
        $member = $this->getMember($userId, $gameId);
        $members = $this->getMembers($gameId);
        $index = array_search($member, $members, true);
        $usedActions = Redis::get("game:$gameId:interactions:usedActions") ?? [];

        if (!$member || false === $index) {
            return false;
        }

        if (
            $context === States::Werewolf->stringify() &&
            $this->getRoleByUserId($userId, $gameId) === Roles::Elder &&
            !in_array(Roles::Elder->name(), $usedActions, true)
        ) {
            $usedActions[] = Roles::Elder->name();
            Redis::set("game:$gameId:interactions:usedActions", $usedActions);

            return true;
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
