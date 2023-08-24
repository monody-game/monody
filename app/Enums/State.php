<?php

namespace App\Enums;

use App\Facades\Redis;

enum State: int
{
    case Waiting = 0;
    case Starting = 1;
    case Roles = 9;

    case Night = 2;
    case Cupid = 15;
    case RandomCoupleSelection = 19;
    case Psychic = 5;
    case Guard = 16;
    case Investigator = 18;
    case Werewolf = 3;
    case InfectedWerewolf = 10;
    case WhiteWerewolf = 11;
    case SurlyWerewolf = 13;
    case Witch = 4;
    case Parasite = 14;

    case Day = 6;
    case Mayor = 12;
    case Vote = 7;

    case End = 8;

    case Hunter = 17;

    /**
     * Returns the technical name of a state (usually single worded)
     */
    public function stringify(): string
    {
        return match ($this) {
            self::Waiting => 'wait',
            self::Starting => 'starting',
            self::Roles => 'roles',
            self::Night => 'night',
            self::Cupid => Role::Cupid->name(),
            self::RandomCoupleSelection => 'random_couple',
            self::Guard => Role::Guard->name(),
            self::Investigator => Role::Investigator->name(),
            self::Psychic => Role::Psychic->name(),
            self::Werewolf => Role::Werewolf->name(),
            self::InfectedWerewolf => Role::InfectedWerewolf->name(),
            self::WhiteWerewolf => Role::WhiteWerewolf->name(),
            self::SurlyWerewolf => Role::SurlyWerewolf->name(),
            self::Witch => Role::Witch->name(),
            self::Parasite => Role::Parasite->name(),
            self::Day => 'day',
            self::Mayor => 'mayor',
            self::Vote => 'vote',
            self::End => 'end',
            self::Hunter => Role::Hunter->name(),
        };
    }

    /**
     * Returns the name of a state, readeably
     */
    public function readeableStringify(): string
    {
        return match ($this) {
            self::Waiting => __('enums.state.waiting'),
            self::Starting => __('enums.state.starting'),
            self::Roles => __('enums.state.roles'),
            self::Night => __('enums.state.night'),
            self::Cupid => __('enums.state.cupid'),
            self::RandomCoupleSelection => __('enums.state.random_couple'),
            self::Guard => __('enums.state.guard'),
            self::Investigator => __('enums.state.investigator'),
            self::Psychic => __('enums.state.psychic'),
            self::Werewolf => __('enums.state.werewolf'),
            self::InfectedWerewolf => __('enums.state.infected_werewolf'),
            self::WhiteWerewolf => __('enums.state.white_werewolf'),
            self::SurlyWerewolf => __('enums.state.surly_werewolf'),
            self::Witch => __('enums.state.witch'),
            self::Parasite => __('enums.state.parasite'),
            self::Day => __('enums.state.day'),
            self::Mayor => __('enums.state.mayor'),
            self::Vote => __('enums.state.vote'),
            self::End => __('enums.state.end'),
            self::Hunter => __('enums.state.hunter'),
        };
    }

    /**
     * Return the background that should be used on theses states
     */
    public function background(): string
    {
        return match ($this) {
            self::Waiting, self::Starting, self::Roles, self::Day, self::Mayor, self::Vote, self::End, self::Hunter => 'day',
            default => 'night',
        };
    }

    public function duration(): int
    {
        return match ($this) {
            self::Waiting, self::End => -1,
            self::Starting, self::Night => 10,
            self::Day => 60,
            self::Mayor, self::Werewolf, self::Vote => 90,
            self::RandomCoupleSelection => 5,
            default => 30,
        };
    }

    /**
     * Return the icon representing the state (it will be shown on the counter)
     */
    public function iconify(): string
    {
        return match ($this) {
            self::Waiting, self::Starting, self::Roles => 'wait',
            self::Day, self::Vote, self::Mayor => 'day',
            self::End => 'trophy',
            self::Hunter => 'hunter',
            self::Guard => 'guard',
            self::Parasite => 'parasite',
            self::Cupid, self::RandomCoupleSelection => 'heart',
            self::Investigator => 'investigator',
            default => 'night',
        };
    }

    /**
     * Return the message that should be sent just before the state begins
     */
    public function message(): array|string|null
    {
        return match ($this) {
            self::Roles, self::Werewolf, self::InfectedWerewolf, self::WhiteWerewolf, self::Psychic, self::Witch, self::Parasite, self::Cupid, self::Guard, self::RandomCoupleSelection => self::readeableStringify(),
            self::Vote => __('enums.state.vote_message'),
            self::Mayor => __('enums.state.mayor_message'),
            self::Hunter => __('enums.state.hunter_message'),
            default => null
        };
    }

    /**
     * Dictate if a state is a role one (psychic, witch, ...)
     */
    public function isRoleState(): bool
    {
        return match ($this) {
            self::Waiting, self::Starting, self::Roles, self::End, self::Day, self::Mayor, self::Night, self::Vote, self::RandomCoupleSelection => false,
            default => true
        };
    }

    /**
     * Return the new time of the counter after a time skip within a state
     */
    public function getTimeSkip(): ?int
    {
        return match ($this) {
            self::Waiting, self::Starting, self::Roles, self::Night, self::Day, self::End => null, // Cannot be skipped
            self::Vote, self::Mayor => 30,
            self::Werewolf => 10,
            default => 0, // Skip the state to the next
        };
    }

    public function hasActionsLeft(string $gameId): bool
    {
        $role = Role::fromName($this->stringify());
        $usedActions = Redis::get("game:$gameId:interactions:usedActions") ?? [];
        $result = true;

        if ($role === null) {
            return false;
        }

        foreach ($role->getActions() as $action) {
            if (in_array($action->value, $usedActions, true)) {
                $result = false;
                break;
            }
        }

        return $result;
    }
}
