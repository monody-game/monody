<?php

namespace App\Enums;

enum Role: int
{
    // Werewolves
    case Werewolf = 1;
    case InfectedWerewolf = 7;
    case SurlyWerewolf = 10;

    // Villagers
    case SimpleVillager = 2;
    case Psychic = 3;
    case Witch = 4;
    case LittleGirl = 5;
    case Elder = 6;
    case Cupid = 12;
    case Guard = 13;

    // Loners
    case WhiteWerewolf = 8;
    case Angel = 9;
    case Parasite = 11;

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
            self::WhiteWerewolf => 'Loup blanc',
            self::Angel => 'Ange',
            self::SurlyWerewolf => 'Loup hargneux',
            self::Parasite => 'Parasite',
            self::Cupid => 'Cupidon',
            self::Guard => 'Garde',
        };
    }

    public static function fromName(string $name): ?Role
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
            'angel' => self::Angel,
            'surly_werewolf' => self::SurlyWerewolf,
            'parasite' => self::Parasite,
            'cupid' => self::Cupid,
            'guard' => self::Guard,
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
            self::WhiteWerewolf => 'white_werewolf',
            self::Angel => 'angel',
            self::SurlyWerewolf => 'surly_werewolf',
            self::Parasite => 'parasite',
            self::Cupid => 'cupid',
            self::Guard => 'guard',
        };
    }

    public function weight(): int
    {
        return match ($this) {
            self::InfectedWerewolf, self::WhiteWerewolf => 5,
            self::Werewolf, self::SurlyWerewolf, self::Parasite, self::Cupid, self::Guard => 4,
            self::LittleGirl, self::Elder, self::Psychic, self::Witch, self::Angel => 2,
            self::SimpleVillager => 1,
        };
    }

    public function limit(): ?int
    {
        return match ($this) {
            self::Werewolf, self::SimpleVillager => null,
            default => 1
        };
    }

    /**
     * @return InteractionAction[]
     */
    public function getActions(): array
    {
        return match ($this) {
            self::Psychic => [InteractionAction::Spectate],
            self::Witch => [InteractionAction::WitchSkip, InteractionAction::KillPotion, InteractionAction::RevivePotion],
            self::Werewolf => [InteractionAction::Kill],
            self::InfectedWerewolf => [InteractionAction::Infect, InteractionAction::InfectedSkip],
            self::WhiteWerewolf => [InteractionAction::BetrayalKill],
            self::SurlyWerewolf => [InteractionAction::Bite, InteractionAction::SurlySkip],
            self::Parasite => [InteractionAction::Contaminate],
            self::Cupid => [InteractionAction::Pair],
            self::Guard => [InteractionAction::Guard],
            default => [],
        };
    }

    /**
     * @return array<string, string|int|array|null>
     */
    public function full(): array
    {
        $role = Role::from($this->value);
        $image = file_exists(storage_path("app/public/roles/{$role->name()}.png")) ? "/assets/roles/{$role->name()}.png" : '/assets/roles/default.png';

        return [
            'id' => $role->value,
            'name' => $role->name(),
            'display_name' => $role->stringify(),
            'image' => $image,
            'limit' => $role->limit(),
            'weight' => $role->weight(),
            'team' => Team::role($role)->full(),
        ];
    }

    public static function all(): array
    {
        $roles = [];

        foreach (Role::cases() as $role) {
            $roles[] = $role->full();
        }

        return $roles;
    }
}
