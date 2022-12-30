<?php

namespace App\Actions;

use App\Enums\InteractionActions;
use App\Enums\Roles;
use App\Events\InteractionUpdate;
use App\Traits\MemberHelperTrait;
use App\Traits\RegisterHelperTrait;
use Exception;

class PsychicAction implements ActionInterface
{
    use MemberHelperTrait, RegisterHelperTrait;

    public function canInteract(InteractionActions $action, string $userId, string $targetId = ''): bool
    {
        $role = $this->getRole($userId);

        return $role !== false && $role === Roles::Psychic && $this->alive($targetId, $this->getCurrentUserGameActivity($userId));
    }

    public function call(string $targetId, InteractionActions $action, ?string $emitterId = null): int
    {
        $role = $this->getRole($targetId);

        if ($role === false) {
            throw new Exception('An error occured, try to see if the game is started');
        }

        return $role->value;
    }

    private function getRole(string $userId): Roles|false
    {
        return $this->getRoleByUserId($userId, $this->getCurrentUserGameActivity($userId));
    }

    public function updateClients(string $userId): void
    {
        $gameId = $this->getGameId($userId);

        broadcast(new InteractionUpdate([
            'gameId' => $gameId,
            'type' => InteractionActions::Spectate->value,
            'target',
        ], true, [$userId]));
    }

    public function close(string $gameId): void
    {
    }

    private function getGameId(string $userId): string
    {
        return $this->getCurrentUserGameActivity($userId);
    }

    public function isSingleUse(): bool
    {
        return true;
    }
}
