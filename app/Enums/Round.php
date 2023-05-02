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
                State::Guard,
                State::Psychic,
                State::Werewolf,
                State::InfectedWerewolf,
                State::SurlyWerewolf,
                State::Witch,
                State::Parasite,
                State::Hunter,
                State::Day,
                State::Vote,
                State::Hunter,
            ],
            self::SecondRound => [
                State::Night,
                State::Guard,
                State::Psychic,
                State::Werewolf,
                State::InfectedWerewolf,
                State::SurlyWerewolf,
                State::Witch,
                State::WhiteWerewolf,
                State::Parasite,
                State::Hunter,
                State::Day,
                State::Mayor,
            ],
            self::LoopRound => [
                State::Night,
                State::Guard,
                State::Psychic,
                State::Werewolf,
                State::InfectedWerewolf,
                State::SurlyWerewolf,
                State::Witch,
                State::WhiteWerewolf,
                State::Parasite,
                State::Hunter,
                State::Day,
                State::Vote,
                State::Hunter,
            ],
            self::EndingRound => [
                State::End,
            ],
        };
    }
}
