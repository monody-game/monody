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
            self::Hunter => 'Chasseur',
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
        };
    }

    /**
     * @return string Role's description, markdown-style
     */
    public function describe(): string
    {
        return match ($this) {
            self::Werewolf => Team::Werewolves->goal(),
            self::InfectedWerewolf => Team::Werewolves->goal() . " Vous avez la possibiliter **d'infecter** un joueur tué par les loups, une fois par partie. Le joueur infecté **deviendra un loup**, tout en conservant ses pouvoirs.",
            self::SurlyWerewolf => Team::Werewolves->goal() . ' Vous vous énervez facilement et vous pouvez **mordre** un joueur une fois par partie. Le joueur mordu **succombera à ses blessures** la nuit suivante.',

            self::SimpleVillager => Team::Villagers->goal() . ' Vous ne possédez aucun pouvoir particulier, sauf votre intelligence !',
            self::Psychic => Team::Villagers->goal() . " Vous pouvez **observer le rôle** d'un joueur une fois par nuit.",
            self::Witch => Team::Villagers->goal() . ' Vous disposez de **2 potions**, permettant de respectivement de **tuer** et de **soigner** un joueur. Utilisez les intelligemment !',
            self::LittleGirl => Team::Villagers->goal() . ' Vous pouvez **observer** le chat des loups. Vous ne pouvez pas mourir des loups lorsque le chasseur est en vie.',
            self::Elder => Team::Villagers->goal() . " Vous disposez d'une **seconde vie** lorsque vous mourrez la nuit.",
            self::Cupid => Team::Villagers->goal() . " Vous pouvez également gagner avec le couple. Vous devrez **mettre en couple** deux joueurs. Leur vie sera ainsi liée et si l'un des amoureux meurt, l'autre le suivra dans sa tombe.",
            self::Guard => Team::Villagers->goal() . ' Vous pouvez **protéger** un joueur par nuit. Le joueur protégé ne peut pas mourir des loups.',
            self::Hunter => Team::Villagers->goal() . " À votre mort, vous pourrez **tirer** sur un joueur pour l'emporter dans la tombe avec vous.",

            self::WhiteWerewolf => Team::Loners->goal() . ", vous gagnez la partie lorsqu'il ne reste **aucun autre joueur**. Vous disposez d'un tour supplémentaire, une nuit sur deux, pour **tuer** un joueur. Ce joueur ne peut pas réssusciter quel que soit son rôle.",
            self::Angel => Team::Loners->goal() . '. Au début de la partie une cible vous est assignée. Si cette cible meurt avant la deuxième nuit, vous **remportez la partie instantanément**.',
            self::Parasite => Team::Loners->goal() . '. Une fois par nuit, vous pouvez contaminer entre 2 et 3 joueurs. Lorsque tous les joueurs encore en vie sont contaminés, vous **remportez la partie instantanément**.'
        };
    }

    public function weight(): int
    {
        return match ($this) {
            self::InfectedWerewolf, self::WhiteWerewolf => 5,
            self::Werewolf, self::SurlyWerewolf, self::Parasite, self::Cupid, self::Guard, self::Hunter => 4,
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
