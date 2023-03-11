<?php

namespace App\Enums;

use App\Models\GameOutcome;
use App\Models\User;

enum Badges: int
{
    // Other badges
    case Wins = 3;
    case Losses = 4;
    case Level = 5;
    case Rank = 6;

    // One-level badges
    case Owner = 0;
    case Beta = 1;
    case Graphist = 2;

    public function name(): string
    {
        return match ($this) {
            self::Graphist => 'graphist',
            self::Beta => 'beta',
            self::Owner => 'owner',
            self::Wins => 'win',
            self::Losses => 'loose',
            self::Level => 'lvl',
            self::Rank => 'rank'
        };
    }

    public function stringify(): string
    {
        return match ($this) {
            self::Graphist => 'Graphiste',
            self::Beta => 'Beta-testeur',
            self::Owner => 'L\'Originel',
            self::Wins => 'Gagnant inarrêtable',
            self::Losses => 'Perdant inépuisable',
            self::Level => 'Haut classé',
            self::Rank => 'Fou de l\'ELO'
        };
    }

    public function describe(): string
    {
        return match ($this) {
            self::Graphist => 'Graphiste de Monody',
            self::Beta => 'A participé à la beta de Monody',
            self::Owner => 'Créateur de Monody !',
            self::Wins => 'A gagné de nombreuses fois',
            self::Losses => 'A perdu de nombreuses fois',
            self::Level => 'A gravi de nombreux niveaux',
            self::Rank => "S'est classé en ELO"
        };
    }

    public function description(): ?string
    {
        return match ($this) {
            default => null,
            self::Wins => 'Remportez la victoire de nombreuses fois afin de débloquer ce badge.',
            self::Losses => 'Perdez de nombreuses fois afin de déloquer ce badge.',
            self::Level => 'Acquérez de nombreux niveaux afin de débloquer ce badge.',
            self::Rank => 'Atteignez les sommets des classements ELO afin de débloquer ce badge !'
        };
    }

    public function maxLevel(): int
    {
        return match ($this) {
            default => -1,
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

    public function gainedExp(int $level): int
    {
        return match ($this) {
            self::Wins => [50, 75, 100, 200, 500][$level - 1],
            self::Losses => [25, 35, 50, 100, 250][$level - 1],
            default => 0
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
