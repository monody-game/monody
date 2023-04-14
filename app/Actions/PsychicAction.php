<?php

namespace App\Actions;

use App\Enums\InteractionAction;
use App\Enums\Role;
use App\Events\InteractionUpdate;
use App\Traits\MemberHelperTrait;

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
        broadcast(new InteractionUpdate([
            'gameId' => $this->gameId,
            'type' => InteractionAction::Spectate->value,
            'target',
        ], true, [$userId]));
    }

    public function close(string $gameId): void
    {
    }

    public function isSingleUse(): bool
    {
        return true;
    }

    public function additionnalData(string $gameId): null
    {
        return null;
    }

    public function status(string $gameId): null
    {
        return null;
    }
}
