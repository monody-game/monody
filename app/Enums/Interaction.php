<?php

namespace App\Enums;

enum Interaction: string
{
    case Vote = 'vote';
    case Witch = 'witch';
    case Psychic = 'psychic';
    case Werewolves = 'werewolves';
    case InfectedWerewolf = 'infected_werewolf';
    case WhiteWerewolf = 'white_werewolf';
    case Mayor = 'mayor';
    case Angel = 'angel';

    public static function getActions(): array
    {
        return [
            self::Vote->value => InteractionAction::Vote,
            self::Mayor->value => InteractionAction::Elect,
            self::Witch->value => [InteractionAction::RevivePotion, InteractionAction::KillPotion, InteractionAction::WitchSkip],
            self::Psychic->value => InteractionAction::Spectate,
            self::Werewolves->value => InteractionAction::Kill,
            self::InfectedWerewolf->value => [InteractionAction::Infect, InteractionAction::InfectedSkip],
            self::WhiteWerewolf->value => InteractionAction::BetrayalKill,
            self::Angel->value => [],
        ];
    }
}
