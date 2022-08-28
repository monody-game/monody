<?php

namespace App\Enums;

enum InteractionActions: string
{
    // Vote
    case Vote = 'vote';

    // Psychic
    case Spectate = 'psychic:spectate';

    // Witch
    case KillPotion = 'witch:kill';
    case RevivePotion = 'witch:revive';
    case WitchSkip = 'witch:skip'; // Case where the witch decide to do nothing

    // Werwolves
    case Kill = 'werewolves:kill';

    /**
     * @return InteractionActions[]
     */
    public static function getActions(Roles $role): array
    {
        return match ($role) {
            Roles::Witch => [self::KillPotion, self::RevivePotion, self::WitchSkip, self::Vote],
            Roles::Werewolf => [self::Kill, self::Vote],
            Roles::Psychic => [self::Spectate, self::Vote],
            Roles::SimpleVillager => [self::Vote]
        };
    }
}
