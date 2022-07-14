<?php

namespace App\Enums;

enum GameStates: int
{
    case WAITING_STATE = 0;
    case STARTING_STATE = 1;
    case NIGHT_STATE = 2;
    case WEREWOLF_STATE = 3;
    case DAY_STATE = 4;
    case VOTE_STATE = 5;

    public function stringify(): string
    {
        return match ($this) {
            self::WAITING_STATE => 'wait',
            self::STARTING_STATE => 'starting',
            self::NIGHT_STATE => 'night',
            self::WEREWOLF_STATE => 'werewolves',
            self::DAY_STATE => 'day',
            self::VOTE_STATE => 'vote',
        };
    }
}
