<?php

namespace App\Actions;

use App\Enums\InteractionAction;
use App\Enums\Role;
use App\Facades\Redis;
use App\Traits\MemberHelperTrait;
use Illuminate\Support\Lottery;

class PsychicAction implements ActionInterface
{
    use MemberHelperTrait;

    public function __construct(
        private readonly string $gameId
    ) {
    }

    public function canInteract(InteractionAction $action, string $userId, string $targetId = ''): bool
    {
        $role = $this->getRole($userId);

        return $role === Role::Psychic && $this->alive($targetId, $this->gameId);
    }

    public function call(string $targetId, InteractionAction $action, string $emitterId): int
    {
        $role = $this->getRole($targetId);

        return $role->value;
    }

    private function getRole(string $userId): Role
    {
        return $this->getRoleByUserId($userId, $this->gameId);
    }

    public function updateClients(string $userId): void
    {
    }

    public function close(): void
    {
    }

    public function isSingleUse(): bool
    {
        return true;
    }

    public function additionnalData(): ?string
    {
        $game = Redis::get("game:$this->gameId");

        // Psychic's passive: she has 5% chance of seeing the role of a simple villager in addition of her normal observation power
        if (in_array(Role::SimpleVillager->value, array_keys($game['roles']), true)) {
            $villagers = $this->getAliveSimpleVillagers($game);

            return Lottery::odds(5, 100)
                // Pick one of the simple villagers randomly (does not take in count already revealed simple villagers)
                ->winner(fn () => array_rand($villagers))
                ->loser(fn () => null)
                ->choose();
        }

        return null;
    }

    public function getAliveSimpleVillagers(array $game): array
    {
        return array_filter(
            $game['assigned_roles'],
            fn ($roleId, $member) => $roleId === Role::SimpleVillager->value &&
                !in_array($member, array_keys($game['dead_users']), true),
            ARRAY_FILTER_USE_BOTH
        );
    }

    public function status(): null
    {
        return null;
    }
}
