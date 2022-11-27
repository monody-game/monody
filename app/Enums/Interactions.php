<?php

namespace App\Enums;

enum Interactions: string
{
    case Vote = 'vote';
    case Witch = 'witch';
    case Psychic = 'psychic';
    case Werewolves = 'werewolves';

    /**
     * @return array<InteractionActions, InteractionActions[]>
     */
    public static function getActions(): array
    {
        return [
            self::Vote->value => InteractionActions::Vote,
            self::Witch->value => [InteractionActions::RevivePotion, InteractionActions::KillPotion, InteractionActions::WitchSkip],
            self::Psychic->value => InteractionActions::Spectate,
            self::Werewolves->value => InteractionActions::Kill,
        ];
    }
}
