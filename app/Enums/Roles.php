<?php

namespace App\Enums;

enum Roles: int
{
    case Werewolf = 1;
    case SimpleVillager = 2;
    case Psychic = 3;
    case Witch = 4;
    case LittleGirl = 5;

    public function stringify(): string
    {
        return match ($this) {
            self::Werewolf => 'Loup-garou',
            self::SimpleVillager => 'Simple villageois',
            self::Psychic => 'Voyante',
            self::Witch => 'Sorcière',
            self::LittleGirl => 'Petite fille'
        };
    }

    public function name(): string
    {
        return match ($this) {
            self::Werewolf => 'werewolf',
            self::SimpleVillager => 'simple_villager',
            self::Psychic => 'psychic',
            self::Witch => 'witch',
            self::LittleGirl => 'little_girl'
        };
    }

    public function weight(): int
    {
        return match ($this) {
            self::Werewolf, self::LittleGirl => 2,
            self::SimpleVillager => 1,
            self::Psychic, self::Witch => 3,
        };
    }

    public function limit(): ?int
    {
        return match ($this) {
            self::Werewolf, self::SimpleVillager => null,
            self::Psychic, self::Witch, self::LittleGirl => 1,
        };
    }

    public function full(): array
    {
        $role = Roles::from($this->value);
        $image = file_exists(public_path("images/roles/{$role->name()}.png")) ? "/images/roles/{$role->name()}.png" : '/images/roles/default.png';

        return [
            'id' => $role->value,
            'name' => $role->name(),
            'display_name' => $role->stringify(),
            'image' => $image,
            'limit' => $role->limit(),
            'weight' => $role->weight(),
            'team_id' => Teams::role($role)->value,
        ];
    }

    public static function all(): array
    {
        $roles = [];

        foreach (Roles::cases() as $role) {
            $roles[] = $role->full();
        }

        return $roles;
    }
}
