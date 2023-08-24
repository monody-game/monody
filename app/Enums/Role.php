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
    case Hunter = 14;
    case Investigator = 15;

    // Loners
    case WhiteWerewolf = 8;
    case Angel = 9;
    case Parasite = 11;

    public function stringify(): string
    {
        return match ($this) {
            self::Werewolf => __('enums.roles.werewolf.name'),
            self::SimpleVillager => __('enums.roles.simple_villager.name'),
            self::Psychic => __('enums.roles.psychic.name'),
            self::Witch => __('enums.roles.witch.name'),
            self::LittleGirl => __('enums.roles.little_girl.name'),
            self::Elder => __('enums.roles.elder.name'),
            self::InfectedWerewolf => __('enums.roles.infected_werewolf.name'),
            self::WhiteWerewolf => __('enums.roles.white_werewolf.name'),
            self::Angel => __('enums.roles.angel.name'),
            self::SurlyWerewolf => __('enums.roles.surly_werewolf.name'),
            self::Parasite => __('enums.roles.parasite.name'),
            self::Cupid => __('enums.roles.cupid.name'),
            self::Guard => __('enums.roles.guard.name'),
            self::Hunter => __('enums.roles.hunter.name'),
            self::Investigator => __('enums.roles.investigator.name')
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
            'hunter' => self::Hunter,
            'investigator' => self::Investigator,
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
            self::Hunter => 'hunter',
            self::Investigator => 'investigator',
        };
    }

    /**
     * @return string Role's description, markdown-style
     */
    public function describe(): string
    {
        return match ($this) {
            self::Werewolf => Team::Werewolves->goal(),
            self::InfectedWerewolf => Team::Werewolves->goal() . __('enums.roles.infected_werewolf.describe'),
            self::SurlyWerewolf => Team::Werewolves->goal() . __('enums.roles.surly_werewolf.describe'),

            self::SimpleVillager => Team::Villagers->goal() . __('enums.roles.simple_villager.describe'),
            self::Psychic => Team::Villagers->goal() . __('enums.roles.psychic.describe'),
            self::Witch => Team::Villagers->goal() . __('enums.roles.witch.describe'),
            self::LittleGirl => Team::Villagers->goal() . __('enums.roles.little_girl.describe'),
            self::Elder => Team::Villagers->goal() . __('enums.roles.elder.describe'),
            self::Cupid => Team::Villagers->goal() . __('enums.roles.cupid.describe'),
            self::Guard => Team::Villagers->goal() . __('enums.roles.guard.describe'),
            self::Hunter => Team::Villagers->goal() . __('enums.roles.hunter.describe'),
            self::Investigator => Team::Villagers->goal() . __('enums.roles.investigator.describe'),

            self::WhiteWerewolf => Team::Loners->goal() . __('enums.roles.white_werewolf.describe'),
            self::Angel => Team::Loners->goal() . __('enums.roles.angel.describe'),
            self::Parasite => Team::Loners->goal() . __('enums.roles.parasite.describe'),
        };
    }

    public function weight(): int
    {
        return match ($this) {
            self::InfectedWerewolf, self::WhiteWerewolf => 5,
            self::Werewolf, self::SurlyWerewolf, self::Parasite, self::Cupid, self::Guard, self::Hunter, self::Investigator => 4,
            self::LittleGirl, self::Elder, self::Psychic, self::Witch, self::Angel => 2,
            self::SimpleVillager => 1,
        };
    }

    public function limit(): int
    {
        return match ($this) {
            self::Werewolf, self::SimpleVillager => -1,
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
            self::Hunter => [InteractionAction::Shoot],
            self::Investigator => [InteractionAction::Compare],
            default => [],
        };
    }

    /**
     * @return array<string, string|int|array>
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
            'description' => $role->describe(),
            'team' => Team::role($role)->full(),
        ];
    }

    public static function all(bool $markdown = false): array
    {
        $roles = [];

        foreach (Role::cases() as $role) {
            $roles[] = array_map(fn ($value) => is_string($value) && !$markdown ? str_replace('*', '', $value) : $value, $role->full());
        }

        return $roles;
    }
}
