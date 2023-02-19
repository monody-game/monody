<?php

namespace App\Enums;

enum Interactions: string
{
    case Vote = 'vote';
    case Witch = 'witch';
    case Psychic = 'psychic';
    case Werewolves = 'werewolves';
    case InfectedWerewolf = 'infected_werewolf';
    case WhiteWerewolf = 'white_werewolf';
    case Mayor = 'mayor';

    public static function getActions(): array
    {
        return [
            self::Vote->value => InteractionActions::Vote,
            self::Mayor->value => InteractionActions::Elect,
            self::Witch->value => [InteractionActions::RevivePotion, InteractionActions::KillPotion, InteractionActions::WitchSkip],
            self::Psychic->value => InteractionActions::Spectate,
            self::Werewolves->value => InteractionActions::Kill,
            self::InfectedWerewolf->value => [InteractionActions::Infect, InteractionActions::InfectedSkip],
            self::WhiteWerewolf->value => InteractionActions::BetrayalKill,
        ];
    }
}
