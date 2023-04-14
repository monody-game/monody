<?php

namespace App\Actions;

use App\Enums\InteractionAction;
use App\Enums\Role;
use App\Traits\MemberHelperTrait;

class ParasiteAction implements ActionInterface
{
    use MemberHelperTrait;

    public function __construct(
        private readonly string $gameId
    ) {
    }

    public function isSingleUse(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function canInteract(InteractionAction $action, string $userId, string $targetId = ''): bool
    {
        return
            $this->alive($targetId, $this->gameId) &&
            $this->getRoleByUserId($userId, $this->gameId) === Role::Parasite;
    }

    /**
     * {@inheritDoc}
     */
    public function call(string $targetId, InteractionAction $action, string $emitterId): mixed
    {
        // TODO: Implement call() method.
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function updateClients(string $userId): void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function additionnalData(string $gameId): null
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function close(string $gameId): void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function status(string $gameId): null
    {
        return null;
    }
}
