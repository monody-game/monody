<?php

namespace App\Enums;

enum Round: int
{
    case FirstRound = 0;
    case LoopRound = 1;
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
                State::RandomCoupleSelection,
                State::Guard,
                State::Investigator,
                State::Psychic,
                State::Werewolf,
                State::InfectedWerewolf,
                State::SurlyWerewolf,
                State::Witch,
                State::Parasite,
                State::Hunter,
                State::Day,
                State::Mayor,
                State::Vote,
                State::Hunter,
            ],
            self::LoopRound => [
                State::Night,
                State::Guard,
                State::Investigator,
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
