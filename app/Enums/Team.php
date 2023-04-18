<?php

namespace App\Enums;

enum Team: int
{
    case Villagers = 1;
    case Werewolves = 2;
    case Loners = 3;

    public function roles(): array
    {
        return match ($this) {
            self::Villagers => [Role::SimpleVillager, Role::Psychic, Role::Witch, Role::LittleGirl, Role::Elder, Role::Cupid],
            self::Werewolves => [Role::Werewolf, Role::InfectedWerewolf, Role::WhiteWerewolf, Role::SurlyWerewolf],
            self::Loners => [Role::WhiteWerewolf, Role::Angel, Role::Parasite]
        };
    }

    public static function role(Role $role): self
    {
        return match ($role) {
            Role::Werewolf, Role::InfectedWerewolf, Role::SurlyWerewolf => self::Werewolves,
            Role::SimpleVillager, Role::Psychic, Role::Witch, Role::LittleGirl, Role::Elder, Role::Cupid => self::Villagers,
            Role::WhiteWerewolf, Role::Angel, Role::Parasite => self::Loners
        };
    }

    public function full(): array
    {
        $team = Team::from($this->value);

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

        foreach (Team::cases() as $team) {
            $teams[] = [
                'id' => $team,
                'name' => $team->name(),
                'display_name' => $team->stringify(),
            ];
        }

        return $teams;
    }
}
