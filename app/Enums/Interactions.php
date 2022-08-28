<?php

namespace App\Enums;

enum Interactions: string
{
    case Vote = 'vote';
    case Witch = 'witch';
    case Psychic = 'psychic';
    case Werewolves = 'werewolves';
}
