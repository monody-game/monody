<?php

namespace App\Enums;

enum Teams: int
{
    case Villagers = 1;
    case Werewolves = 2;
    case Loners = 3;

    public function roles(): array
    {
        return match ($this) {
            self::Villagers => [Roles::SimpleVillager, Roles::Psychic, Roles::Witch, Roles::LittleGirl, Roles::Elder],
            self::Werewolves => [Roles::Werewolf, Roles::InfectedWerewolf, Roles::WhiteWerewolf],
            self::Loners => [Roles::WhiteWerewolf]
        };
    }

    public static function role(Roles $role): self
    {
        return match ($role) {
            Roles::Werewolf, Roles::InfectedWerewolf => self::Werewolves,
            Roles::SimpleVillager, Roles::Psychic, Roles::Witch, Roles::LittleGirl, Roles::Elder => self::Villagers,
            Roles::WhiteWerewolf => self::Loners
        };
    }

    public function full(): array
    {
        $team = Teams::from($this->value);

        return [
            'id' => $team->value,
            'name' => $team->name(),
            'display_name' => $team->stringify(),
        ];
    }

    public function stringify(): string
    {
        return match ($this) {
            self::Werewolves => 'Loups-garous',
            self::Villagers => 'Villageois',
            self::Loners => 'Solos',
        };
    }

    public function name(): string
    {
        return match ($this) {
            self::Werewolves => 'werewolves',
            self::Villagers => 'villagers',
            self::Loners => 'loners'
        };
    }

    public static function all(): array
    {
        $teams = [];

        foreach (Teams::cases() as $team) {
            $teams[] = [
                'id' => $team,
                'name' => $team->name(),
                'display_name' => $team->stringify(),
            ];
        }

        return $teams;
    }
}
