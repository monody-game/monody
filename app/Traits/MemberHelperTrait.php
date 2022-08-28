<?php

namespace App\Traits;

use App\Enums\Roles;
use App\Enums\Teams;
use App\Facades\Redis;
use App\Models\Role;
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
     * @param  int  $roleId
     * @param  string  $gameId
     * @return array<int|string>|false
     */
    public function getUserIdByRole(int $roleId, string $gameId): array|false
    {
        if (!$this->exists("game:$gameId")) {
            return false;
        }

        $game = Redis::get("game:$gameId");

        if (!$game['is_started']) {
            return false;
        }

        return array_keys($game['assigned_roles'], $roleId, true);
    }

    public function getRoleByUserId(string $userId, string $gameId): Roles|false
    {
        if (!$this->exists("game:$gameId")) {
            return false;
        }

        $game = Redis::get("game:$gameId");

        if (!$game['is_started']) {
            return false;
        }

        return Roles::from($game['assigned_roles'][$userId]);
    }

    public function getWerewolves(string $gameId): array
    {
        if (!$this->exists("game:$gameId")) {
            return [];
        }

        $werewolvesRoles = Role::where('team_id', '=', Teams::Werewolves->value)->get()->toArray();
        $werewolves = [];

        foreach ($werewolvesRoles as $role) {
            /** @phpstan-ignore-next-line  */
            $werewolves[] = $this->getUserIdByRole($role['id'], $gameId);
        }

        return $werewolves;
    }

    public function kill(string $userId, string $gameId): bool
    {
        if (!$this->exists("game:$gameId")) {
            return false;
        }

        $member = $this->getMember($userId, $gameId);
        $members = $this->getMembers($gameId);
        $index = array_search($member, $members, true);

        if (!$member || false === $index) {
            return false;
        }

        $member = array_splice($members, (int) $index, 1)[0];

        $member['user_info']['is_dead'] = true;
        $members = [...$members, $member];

        Redis::set("game:$gameId:members", $members);

        return true;
    }
}
