<?php

namespace App\Enums;

enum GameInteractions: string
{
    case Vote = 'vote';
    case Witch = 'witch';
    case Psychic = 'psychic';
    case Werewolves = 'werewolves';
}
