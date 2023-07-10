<?php

namespace App\Enums;

enum GameType: int
{
    case NORMAL = 0x00000;
    case VOCAL = 0x00001;
    case DISCORD = 0x00010;
}
