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
    case SurlyWerewolf = 'surly_werewolf';
    case Parasite = 'parasite';
    case Mayor = 'mayor';
    case Angel = 'angel';
    case Cupid = 'cupid';

    public static function getActions(): array
    {
        return [
            self::Vote->value => InteractionAction::Vote->value,
            self::Mayor->value => InteractionAction::Elect->value,
            self::Witch->value => [InteractionAction::RevivePotion->value, InteractionAction::KillPotion->value, InteractionAction::WitchSkip->value],
            self::Psychic->value => InteractionAction::Spectate->value,
            self::Werewolves->value => InteractionAction::Kill->value,
            self::InfectedWerewolf->value => [InteractionAction::Infect->value, InteractionAction::InfectedSkip->value],
            self::WhiteWerewolf->value => InteractionAction::BetrayalKill->value,
            self::SurlyWerewolf->value => [InteractionAction::Bite->value, InteractionAction::SurlySkip->value],
            self::Angel->value => [],
            self::Parasite->value => InteractionAction::Contaminate->value,
            self::Cupid->value => InteractionAction::Pair->value,
        ];
    }
}
