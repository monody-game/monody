<?php

namespace App\Enums;

enum States: int
{
    case Waiting = 0;
    case Starting = 1;
    case Roles = 9;

    case Night = 2;
    case Werewolf = 3;
    case Witch = 4;
    case Psychic = 5;

    case Day = 6;
    case Vote = 7;

    case End = 8;

    public function stringify(): string
    {
        return match ($this) {
            self::Waiting => 'wait',
            self::Starting => 'starting',
            self::Roles => 'roles',
            self::Night => 'night',
            self::Werewolf => 'werewolf',
            self::Witch => 'witch',
            self::Psychic => 'psychic',
            self::Day => 'day',
            self::Vote => 'vote',
            self::End => 'end',
        };
    }

    public function readeableStringify(): string
    {
        return match ($this) {
            self::Waiting => 'Attente',
            self::Starting => 'Démarrage',
            self::Roles => 'Distribution des rôles',
            self::Night => 'Nuit',
            self::Werewolf => 'Tour des loups-garous',
            self::Witch => 'Tour de la sorcière',
            self::Psychic => 'Tour de la voyante',
            self::Day => 'Jour',
            self::Vote => 'Vote',
            self::End => 'Fin de la partie'
        };
    }

    public function background(): string
    {
        return match ($this) {
            self::Waiting, self::Starting, self::Roles, self::Day, self::Vote, self::End => 'day',
            self::Night, self::Werewolf, self::Witch, self::Psychic => 'night',
        };
    }

    public function duration(): int
    {
        return match ($this) {
            self::Waiting, self::End => -1,
            self::Starting, self::Night, self::Day, self::Witch, self::Psychic, self::Vote => 5,
            self::Roles => 10,
            self::Werewolf => 50
        };
    }

    public function iconify(): string
    {
        return match ($this) {
            self::Waiting, self::Starting, self::Roles => 'wait',
            self::Night, self::Witch, self::Werewolf, self::Psychic => 'night',
            self::Day, self::Vote => 'day',
            self::End => 'trophy'
        };
    }

    public function message(): ?string
    {
        return match ($this) {
            self::Roles, self::Werewolf, self::Psychic, self::Witch => self::readeableStringify(),
            self::Vote => 'Début du ' . mb_strtolower(self::readeableStringify()),
            default => null
        };
    }

    public function isRoleState(): bool
    {
        return match ($this) {
            self::Waiting, self::Starting, self::Roles, self::End, self::Day, self::Night, self::Vote => false,
            default => true
        };
    }
}
