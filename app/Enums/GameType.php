<?php

namespace App\Enums;

enum GameType: int
{
    case NORMAL = 1 << 0;
    case VOCAL = 1 << 1;

    case PRIVATE_GAME = 1 << 2;
    case HIDDEN_COMP = 1 << 3;
}
