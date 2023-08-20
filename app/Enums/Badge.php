<?php

namespace App\Enums;

use App\Models\GameOutcome;
use App\Models\User;

enum Badge: int
{
    // Other badges
    case Wins = 3;
    case Losses = 4;
    case Level = 5;

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
            self::Level => 'lvl'
        };
    }

    public function secret(): bool
    {
        return match ($this) {
            self::Owner, self::Beta, self::Graphist => true,
            default => false,
        };
    }

    public function stringify(): string
    {
        return match ($this) {
            self::Graphist => __('enums.badges.graphist.name'),
            self::Beta => __('enums.badges.beta.name'),
            self::Owner => __('enums.badges.owner.name'),
            self::Wins => __('enums.badges.wins.name'),
            self::Losses => __('enums.badges.losses.name'),
            self::Level => __('enums.badges.level.name'),
        };
    }

    public function describe(): string
    {
        return match ($this) {
            self::Graphist => __('enums.badges.graphist.describe'),
            self::Beta => __('enums.badges.beta.describe'),
            self::Owner => __('enums.badges.owner.describe'),
            self::Wins => __('enums.badges.wins.describe'),
            self::Losses => __('enums.badges.losses.describe'),
            self::Level => __('enums.badges.level.describe'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Graphist => __('enums.badges.graphist.description'),
            self::Owner => __('enums.badges.owner.description'),
            self::Beta => __('enums.badges.beta.description'),
            self::Wins => __('enums.badges.wins.description'),
            self::Losses => __('enums.badges.losses.description'),
            self::Level => __('enums.badges.level.description'),
        };
    }

    public function maxLevel(): int
    {
        return match ($this) {
            default => -1,
            self::Wins, self::Losses, self::Level => 5
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
            $outcomes = GameOutcome::whereHas('users', fn ($query) => $query->where('user_id', $user->id))->get()
                ->load('users')
                ->map(fn ($outcome) => $outcome->users->first());
        }

        return match ($this) {
            default => false,
            self::Wins => $outcomes->where('pivot.win', true)->count() >= $this->steps()[$level - 1],
            self::Losses => $outcomes->where('pivot.win', false)->count() >= $this->steps()[$level - 1],
            self::Level => $user->level >= $this->steps()[$level - 1],
        };
    }

    public function full(): array
    {
        return [
            'id' => $this->value,
            'name' => $this->name(),
            'display_name' => $this->stringify(),
            'explanation' => $this->describe(),
            'description' => $this->description(),
            'owned' => false,
            'max_level' => $this->maxLevel(),
            'current_level' => 0,
            'obtained_at' => null,
            'secret' => $this->secret(),
        ];
    }
}
