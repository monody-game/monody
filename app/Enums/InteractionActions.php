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
}
