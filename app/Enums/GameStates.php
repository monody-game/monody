<?php

namespace App\Enums;

enum GameStates: int
{
    case Waiting = 0;
    case Starting = 1;
    case Night = 2;
    case Werwolf = 3;
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
            self::Werwolf => 'werewolves',
            self::Witch => 'witch',
            self::Psychic => 'psychic',
            self::Day => 'day',
            self::Vote => 'vote',
        };
    }

    public function duration(): int
    {
        return match ($this) {
            self::Waiting => -1,
            self::Starting, self::Night, self::Day => 10,
            self::Werwolf, self::Witch, self::Psychic => 20,
            self::Vote => 40,
        };
    }
}
