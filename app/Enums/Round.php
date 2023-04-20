<?php

namespace App\Enums;

enum Round: int
{
    case FirstRound = 1;
    case SecondRound = 2;
    case LoopRound = 3;
    case EndingRound = 999999;

    /**
     * @return State[]
     */
    public function stateify(): array
    {
        return match ($this) {
            self::FirstRound => [
                State::Waiting,
                State::Starting,
                State::Roles,
                State::Night,
                State::Cupid,
                State::Psychic,
                State::Werewolf,
                State::InfectedWerewolf,
                State::SurlyWerewolf,
                State::Witch,
                State::Parasite,
                State::Day,
                State::Vote,
            ],
            self::SecondRound => [
                State::Night,
                State::Psychic,
                State::Werewolf,
                State::InfectedWerewolf,
                State::SurlyWerewolf,
                State::Witch,
                State::WhiteWerewolf,
                State::Parasite,
                State::Day,
                State::Mayor,
            ],
            self::LoopRound => [
                State::Night,
                State::Psychic,
                State::Werewolf,
                State::InfectedWerewolf,
                State::SurlyWerewolf,
                State::Witch,
                State::WhiteWerewolf,
                State::Parasite,
                State::Day,
                State::Vote,
            ],
            self::EndingRound => [
                State::End,
            ],
        };
    }
}
