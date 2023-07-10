<?php

namespace App\Enums;

enum GameType: int
{
    case NORMAL = 0b00001;
    case VOCAL = 0b00010;
    case DISCORD = 0b00100;
}
