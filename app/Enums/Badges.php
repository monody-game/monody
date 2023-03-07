<?php

namespace App\Enums;

use App\Models\GameOutcome;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

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
            default => 0,
            self::Wins, self::Losses, self::Level, self::Rank => 5
        };
    }

    public function steps(): array
    {
        return match ($this) {
            self::Wins, self::Losses, self::Level => [10, 30, 50, 70, 100],
            default => []
        };
    }

    public function requirementMet(User $user, int $level): bool
    {
        $outcomes = collect();

        if ($this === self::Wins || $this === self::Losses) {
            $outcomes = GameOutcome::select('win')->where('user_id', $user->id)->get();
        }

        return match ($this) {
            default => false,
            self::Wins => $outcomes->where('win', true)->count() >= $this->steps()[$level - 1],
            self::Losses => $outcomes->where('win', false)->count() >= $this->steps()[$level - 1],
            self::Level => $user->level >= $this->steps()[$level - 1],
        };
    }
}
