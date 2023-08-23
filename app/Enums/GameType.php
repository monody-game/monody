<?php

namespace App\Enums;

enum GameType: int
{
    // Unique types
    case NORMAL = 1 << 0;
    case VOCAL = 1 << 1;

    // Combinable types
    case PRIVATE_GAME = 1 << 2;
    case RANDOM_COUPLE = 1 << 3;
    case TROUPLE = 1 << 4;
    case HIDDEN_COMPOSITION = 1 << 5;
}
