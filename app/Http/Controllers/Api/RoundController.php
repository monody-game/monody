<?php

namespace App\Http\Controllers\Api;

use App\Enums\GameType;
use App\Enums\InteractionAction;
use App\Enums\Role;
use App\Enums\Round;
use App\Enums\State;
use App\Facades\Redis;
use App\Http\Controllers\Controller;
use App\Http\Responses\JsonApiResponse;
use App\Traits\GameHelperTrait;
use App\Traits\MemberHelperTrait;

class RoundController extends Controller
{
    use GameHelperTrait, MemberHelperTrait;

    public function all(string $gameId = null): JsonApiResponse
    {
        $rounds = [];

        foreach (Round::cases() as $round) {
            $rounds[$round->value] = $this->getRound($round->value, $gameId);
        }

        return JsonApiResponse::make(['rounds' => $rounds])->withoutCache();
    }

    public function get(int $round, string $gameId = null): JsonApiResponse
    {
        return JsonApiResponse::make([
            'round' => $this->getRound($round, $gameId),
        ])->withoutCache();
    }

    private function getRound(int $round, string $gameId = null): array
    {
        $round = Round::tryFrom($round);
        $removedStates = [];

        if ($round === null) {
            $round = Round::LoopRound;
        }

        $round = $round->stateify();

        if ($gameId !== null && Redis::exists("game:$gameId")) {
            $game = Redis::get("game:$gameId");
            $gameState = Redis::get("game:$gameId:state");
            $deaths = (Redis::get("game:$gameId:deaths") ?? []);
            $usedActions = Redis::get("game:$gameId:interactions:usedActions") ?? [];
            $deaths = array_map(fn ($death) => $death['user'], $deaths);
            $roles = array_keys($game['roles']);

            $roles = array_map(function ($role) {
                return Role::from($role)->name();
            }, $roles);

            foreach ($round as $key => $state) {
                // If the current mayor cannot designate a successor
                // Commented because it causes the game to crash and because I'm too lazy to fix it
				if (
                    $state === State::MayorSuccession &&
					/** 
                    (
                        (
                            array_key_exists('mayor', $game) &&
                            !in_array($game['mayor'], array_keys($game['dead_users']), true)
                        ) ||
                            !array_key_exists('mayor', $game)
                    )*/
                ) {
                    $removedStates[] = array_splice($round, ($key - count($removedStates)), 1);

                    continue;
                }
				

                if (
                    $state === State::RandomCoupleSelection &&
                    !$this->isOfType($game['type'], GameType::RANDOM_COUPLE->value)
                ) {
                    $removedStates[] = array_splice($round, ($key - count($removedStates)), 1);

                    continue;
                }

                // No checks needed if the state is not a role one
                if (!$state->isRoleState()) {
                    continue;
                }

                if ($gameState['status'] === $state->value) {
                    continue;
                }

                /** @var Role $role, the state is a role state by the condition above */
                $role = Role::fromName($state->stringify());

                // If the role's user is not in the game anymore
                if (
                    !in_array(
                        array_search($role->value, $game['assigned_roles'], true),
                        array_diff($game['users'], array_keys($game['dead_users'])), true
                    ) &&
                    $state !== State::Werewolf &&
                    $state !== State::Hunter
                ) {
                    $removedStates[] = array_splice($round, ($key - count($removedStates)), 1);

                    continue;
                }

                // If the role just is not in roles list
                if (
                    !in_array($state->stringify(), $roles, true) &&
                    count(array_filter($roles, fn ($role) => str_contains($role, $state->stringify()))) === 0 &&
                    $state !== State::Werewolf
                ) {
                    $removedStates[] = array_splice($round, ($key - count($removedStates)), 1);

                    continue;
                }

                if (
                    $state === State::Cupid &&
                    $this->isOfType($game['type'], GameType::RANDOM_COUPLE->value)
                ) {
                    $removedStates[] = array_splice($round, ($key - count($removedStates)), 1);

                    continue;
                }

                // If the investigator can't compare anyone
                if (
                    $state === State::Investigator &&
                    in_array(Role::Investigator->value, array_values($game['assigned_roles']), true) &&
                    count($this->getUserIdByRole(Role::Investigator, $gameId)) > 0 &&
                    (
                        count($game['users']) - count(array_keys($game['dead_users'])) <= 1 &&
                        count($game['users']) - count(array_keys($game['dead_users'])) < count($this->getNotComparableUsers($gameId)) + 1 ||
                        count($game['users']) - count(array_keys($game['dead_users'])) - count($this->getNotComparableUsers($gameId)) === 1
                    )
                ) {
                    $removedStates[] = array_splice($round, ($key - count($removedStates)), 1);

                    continue;
                }

                // If the infected werewolf doesn't have anyone to infect
                if (
                    $state === State::InfectedWerewolf &&
                    count($deaths) === 0
                ) {
                    $removedStates[] = array_splice($round, ($key - count($removedStates)), 1);

                    continue;
                }

                // If the hunter does not need to / cannot shoot
                if (
                    $state === State::Hunter &&
                    in_array(Role::Hunter->value, array_values($game['assigned_roles']), true) &&
                    count($this->getUserIdByRole(Role::Hunter, $gameId)) > 0 &&
                    (
                        (
                            in_array($this->getUserIdByRole(Role::Hunter, $gameId)[0], array_keys($game['dead_users']), true) &&
                            !in_array($this->getUserIdByRole(Role::Hunter, $gameId)[0], $deaths, true) &&
                            in_array(InteractionAction::Shoot, $usedActions, true)
                        ) || (
                            !in_array($this->getUserIdByRole(Role::Hunter, $gameId)[0], array_keys($game['dead_users']), true) &&
                            in_array($this->getUserIdByRole(Role::Hunter, $gameId)[0], $deaths, true) &&
                            in_array(InteractionAction::Shoot, $usedActions, true)
                        ) || (
                            !in_array($this->getUserIdByRole(Role::Hunter, $gameId)[0], array_keys($game['dead_users']), true) &&
                            !in_array($this->getUserIdByRole(Role::Hunter, $gameId)[0], $deaths, true)
                        )
                    )
                ) {
                    $removedStates[] = array_splice($round, ($key - count($removedStates)), 1);

                    continue;
                }

                // If the role does not have any action left
                if (
                    !$state->hasActionsLeft($gameId) ||
                    // Remove dead users' role states
                    (
                        /** @phpstan-ignore-next-line $state is a role state (line 56), so fromName cannot return null */
                        count($this->getUserIdByRole(Role::fromName($state->stringify()), $gameId)) > 0 &&
                        /** @phpstan-ignore-next-line */
                        !$this->alive($this->getUserIdByRole(Role::fromName($state->stringify()), $gameId)[0], $gameId) &&
                        $state !== State::Hunter
                    )
                ) {
                    $removedStates[] = array_splice($round, ($key - count($removedStates)), 1);

                    continue;
                }

                // If it's not an even round number for the white werewolf
                if ($state === State::WhiteWerewolf && ($gameState['round'] % 2 === 0 || $gameState['round'] === 0)) {
                    $removedStates[] = array_splice($round, ($key - count($removedStates)), 1);
                }
            }
        }

        return array_map(function ($state) {
            return [
                'identifier' => $state->value,
                'raw_name' => $state->stringify(),
                'duration' => $state->duration(),
            ];
        }, $round);
    }

    private function getNotComparableUsers(string $gameId): array
    {
        $compared = Redis::get("game:$gameId:interactions:investigator") ?? [];
        $investigator = $this->getUserIdByRole(Role::Investigator, $gameId)[0];

        $compared = array_filter(
            $compared,
            fn ($comparedUser) => $comparedUser === $investigator ||
                count(array_filter($compared, fn ($user) => $user === $comparedUser)) === 2
        );

        return array_unique($compared);
    }
}
