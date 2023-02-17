<?php

namespace App\Enums;

enum Rounds: int
{
    case FirstRound = 1;
    case SecondRound = 2;
    case LoopRound = 3;
    case EndingRound = 999999;

    /**
     * @return States[]
     */
    public function stateify(): array
    {
        return match ($this) {
            self::FirstRound => [
                States::Waiting,
                States::Starting,
                States::Roles,
                States::Night,
                States::Psychic,
                States::Werewolf,
                States::InfectedWerewolf,
                States::Witch,
                States::Day,
                States::Vote,
            ],
            self::SecondRound, self::LoopRound => [
                States::Night,
                States::Psychic,
                States::Werewolf,
                States::InfectedWerewolf,
                States::Witch,
                States::WhiteWerewolf,
                States::Day,
                States::Vote,
            ],
            self::EndingRound => [
                States::End,
            ],
        };
    }
}
