<?php

namespace App\Enums;

enum Badges: int
{
    // One-level badges
    case Graphist = 0;
    case Beta = 1;
    case Owner = 2;

    // Other badges
    case Wins = 3;
    case Losses = 4;
    case Level = 5;
    case Rank = 6;

    public function maxLevel(): int
    {
        return match ($this) {
            self::Graphist, self::Beta, self::Owner => 0,
            self::Wins, self::Losses, self::Level, self::Rank => 5
        };
    }
}
