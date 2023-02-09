<?php

namespace App\Enums;

use App\Facades\Redis;

enum States: int
{
    case Waiting = 0;
    case Starting = 1;
    case Roles = 9;

    case Night = 2;
    case Werewolf = 3;
    case Witch = 4;
    case Psychic = 5;
    case InfectedWerewolf = 10;

    case Day = 6;
    case Vote = 7;

    case End = 8;

    /**
     * Returns the technical name of a state (usually single worded)
     *
     * @return string
     */
    public function stringify(): string
    {
        return match ($this) {
            self::Waiting => 'wait',
            self::Starting => 'starting',
            self::Roles => 'roles',
            self::Night => 'night',
            self::Werewolf => Roles::Werewolf->name(),
            self::InfectedWerewolf => Roles::InfectedWerewolf->name(),
            self::Witch => Roles::Witch->name(),
            self::Psychic => Roles::Psychic->name(),
            self::Day => 'day',
            self::Vote => 'vote',
            self::End => 'end',
        };
    }

    /**
     * Returns the name of a state, readeably
     *
     * @return string
     */
    public function readeableStringify(): string
    {
        return match ($this) {
            self::Waiting => 'Attente',
            self::Starting => 'Démarrage',
            self::Roles => 'Distribution des rôles',
            self::Night => 'Nuit',
            self::Werewolf => 'Tour des loups-garous',
            self::InfectedWerewolf => 'Tour du loup malade',
            self::Witch => 'Tour de la sorcière',
            self::Psychic => 'Tour de la voyante',
            self::Day => 'Jour',
            self::Vote => 'Vote',
            self::End => 'Fin de la partie',
        };
    }

    /**
     * Return the background that should be used on theses states
     *
     * @return string
     */
    public function background(): string
    {
        return match ($this) {
            self::Waiting, self::Starting, self::Roles, self::Day, self::Vote, self::End => 'day',
            self::Night, self::Werewolf, self::InfectedWerewolf, self::Witch, self::Psychic => 'night',
        };
    }

    public function duration(): int
    {
        return match ($this) {
            self::Waiting, self::End => -1,
            self::Starting, self::Night => 10,
            self::Roles, self::InfectedWerewolf => 30,
            self::Day, self::Vote, self::Werewolf, self::Psychic, self::Witch => 90
        };
    }

    /**
     * Return the icon representing the state (it will be shown on the counter)
     *
     * @return string
     */
    public function iconify(): string
    {
        return match ($this) {
            self::Waiting, self::Starting, self::Roles => 'wait',
            self::Night, self::Witch, self::Werewolf, self::InfectedWerewolf, self::Psychic => 'night',
            self::Day, self::Vote => 'day',
            self::End => 'trophy'
        };
    }

    /**
     * Return the message that should be sent just before the state begins
     *
     * @return string|null
     */
    public function message(): ?string
    {
        return match ($this) {
            self::Roles, self::Werewolf, self::InfectedWerewolf, self::Psychic, self::Witch => self::readeableStringify(),
            self::Vote => 'Début du ' . mb_strtolower(self::readeableStringify()),
            default => null
        };
    }

    /**
     * Dictate if a state is a role one (psychic, witch, ...)
     *
     * @return bool
     */
    public function isRoleState(): bool
    {
        return match ($this) {
            self::Waiting, self::Starting, self::Roles, self::End, self::Day, self::Night, self::Vote => false,
            default => true
        };
    }

    /**
     * Return the new time of the counter after a time skip within a state
     *
     * @return int|null
     */
    public function getTimeSkip(): ?int
    {
        return match ($this) {
            self::Waiting, self::Starting, self::Roles, self::Night, self::Day, self::End => null, // Cannot be skipped
            self::Vote => 30,
            self::Werewolf => 10,
            self::Witch, self::Psychic, self::InfectedWerewolf => 0, // Skip the state to the next
        };
    }

    public function hasActionsLeft(string $gameId): bool
    {
        $role = Roles::fromName($this->stringify());
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
