<?php

namespace App\Enums;

enum States: int
{
    case Waiting = 0;
    case Starting = 1;
    case Night = 2;
    case Werewolf = 3;
    case Witch = 4;
    case Psychic = 5;
    case Day = 6;
    case Vote = 7;

    public function stringify(): string
    {
        return match ($this) {
            self::Waiting => 'wait',
            self::Starting => 'starting',
            self::Night => 'night',
            self::Werewolf => 'werewolf',
            self::Witch => 'witch',
            self::Psychic => 'psychic',
            self::Day => 'day',
            self::Vote => 'vote',
        };
    }

    public function readeableStringify(): string
    {
        return match ($this) {
            self::Waiting => 'Attente',
            self::Starting => 'Démarrage',
            self::Night => 'Nuit',
            self::Werewolf => 'Tour des loups-garous',
            self::Witch => 'Tour de la sorcière',
            self::Psychic => 'Tour de la voyante',
            self::Day => 'Jour',
            self::Vote => 'Vote',
        };
    }

    public function duration(): int
    {
        return match ($this) {
            self::Waiting => -1,
            self::Starting, self::Night, self::Day, self::Witch, self::Psychic, self::Vote => 5,
            self::Werewolf => 20,
        };
    }

    public function iconify(): string
    {
        return match ($this) {
            self::Waiting, self::Starting => 'wait',
            self::Night, self::Witch, self::Werewolf, self::Psychic => 'night',
            self::Day, self::Vote => 'day',
        };
    }

    public function message(): ?string
    {
        return match ($this) {
            self::Werewolf, self::Psychic, self::Witch => self::readeableStringify(),
            self::Vote => 'Début du ' . mb_strtolower(self::readeableStringify()),
            default => null
        };
    }

    public function isRoleState(): bool
    {
        return match ($this) {
            self::Waiting, self::Starting, self::End, self::Day, self::Night, self::Vote => false,
            default => true
        };
    }
}
