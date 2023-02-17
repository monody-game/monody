<?php

namespace App\Enums;

enum Roles: int
{
    // Werewolves
    case Werewolf = 1;
    case InfectedWerewolf = 7;
    case WhiteWerewolf = 8;

    // Villagers
    case SimpleVillager = 2;
    case Psychic = 3;
    case Witch = 4;
    case LittleGirl = 5;
    case Elder = 6;

    public function stringify(): string
    {
        return match ($this) {
            self::Werewolf => 'Loup-garou',
            self::SimpleVillager => 'Simple villageois',
            self::Psychic => 'Voyante',
            self::Witch => 'Sorcière',
            self::LittleGirl => 'Petite fille',
            self::Elder => 'Ancien',
            self::InfectedWerewolf => 'Loup malade',
            self::WhiteWerewolf => 'Loup blanc'
        };
    }

    public static function fromName(string $name): ?Roles
    {
        return match ($name) {
            'werewolf' => self::Werewolf,
            'simple_villager' => self::SimpleVillager,
            'psychic' => self::Psychic,
            'witch' => self::Witch,
            'little_girl' => self::LittleGirl,
            'elder' => self::Elder,
            'infected_werewolf' => self::InfectedWerewolf,
            'white_werewolf' => self::WhiteWerewolf,
            default => null,
        };
    }

    public function name(): string
    {
        return match ($this) {
            self::Werewolf => 'werewolf',
            self::SimpleVillager => 'simple_villager',
            self::Psychic => 'psychic',
            self::Witch => 'witch',
            self::LittleGirl => 'little_girl',
            self::Elder => 'elder',
            self::InfectedWerewolf => 'infected_werewolf',
            self::WhiteWerewolf => 'white_werewolf'
        };
    }

    public function weight(): int
    {
        return match ($this) {
            self::Werewolf, self::LittleGirl, self::Elder => 2,
            self::SimpleVillager => 1,
            self::Psychic, self::Witch, self::InfectedWerewolf, self::WhiteWerewolf => 3,
        };
    }

    public function limit(): ?int
    {
        return match ($this) {
            self::Werewolf, self::SimpleVillager => null,
            self::Psychic, self::Witch, self::LittleGirl, self::Elder, self::InfectedWerewolf, self::WhiteWerewolf => 1,
        };
    }

    /**
     * @return InteractionActions[]
     */
    public function getActions(): array
    {
        return match ($this) {
            self::Psychic => [InteractionActions::Spectate],
            self::Witch => [InteractionActions::WitchSkip, InteractionActions::KillPotion, InteractionActions::RevivePotion],
            self::Werewolf => [InteractionActions::Kill],
            self::InfectedWerewolf => [InteractionActions::Infect, InteractionActions::InfectedSkip],
            self::WhiteWerewolf => [InteractionActions::BetrayalKill],
            default => [],
        };
    }

    public function full(): array
    {
        $role = Roles::from($this->value);
        $image = file_exists(storage_path("app/public/roles/{$role->name()}.png")) ? "/assets/roles/{$role->name()}.png" : '/assets/roles/default.png';

        return [
            'id' => $role->value,
            'name' => $role->name(),
            'display_name' => $role->stringify(),
            'image' => $image,
            'limit' => $role->limit(),
            'weight' => $role->weight(),
            'team' => Teams::role($role)->full(),
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
