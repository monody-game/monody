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
            self::Werewolf => 'werewolves',
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
            self::Werewolf, self::Witch, self::Psychic => 20,
            self::Vote => 40,
        };
    }
}
