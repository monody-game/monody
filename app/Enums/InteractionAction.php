<?php

namespace App\Enums;

enum InteractionAction: string
{
    // Vote
    case Vote = 'vote';

    case Elect = 'mayor:vote';

    // Psychic
    case Spectate = 'psychic:spectate';

    // Witch
    case KillPotion = 'witch:kill';
    case RevivePotion = 'witch:revive';
    case WitchSkip = 'witch:skip'; // Case where the witch decide to do nothing

    // Werwolves
    case Kill = 'werewolves:kill';

    // Infected werewolf
    case Infect = 'infected_werewolf:infect';
    case InfectedSkip = 'infected_werewolf:skip';

    // White Werewolf
    case BetrayalKill = 'white_werewolf:kill';

    // Surly werewolf
    case Bite = 'surly_werewolf:bite';
    case SurlySkip = 'surly_werewolf:skip';

    // Parasite
    case Contaminate = 'parasite:contaminate';

    // Cupid
    case Pair = 'cupid:pair';

    // Guard
    case Guard = 'guard:guard';

    // Hunter
    case Shoot = 'hunter:shoot';

    // Investigator
    case Compare = 'investigate:compare';
}
