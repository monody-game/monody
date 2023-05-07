<?php

namespace App\Actions;

use App\Enums\InteractionAction;
use App\Enums\Role;
use App\Facades\Redis;
use App\Traits\MemberHelperTrait;

class HunterAction implements ActionInterface
{
    use MemberHelperTrait;

    public function __construct(
        private readonly string $gameId
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function isSingleUse(): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function canInteract(InteractionAction $action, string $userId, string $targetId = ''): bool
    {
        return $this->alive($targetId, $this->gameId) &&
            $this->getRoleByUserId($userId, $this->gameId) === Role::Hunter;
    }

    /**
     * {@inheritDoc}
     */
    public function call(string $targetId, InteractionAction $action, string $emitterId): null
    {
        $this->kill($targetId, $this->gameId, 'hunter');

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
    public function additionnalData(string $gameId): mixed
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function close(string $gameId): void
    {
        Redis::update("game:$this->gameId:interactions:usedActions", function (array &$usedActions) {
            $usedActions[] = InteractionAction::Shoot->value;
        });
    }

    /**
     * {@inheritDoc}
     */
    public function status(string $gameId): mixed
    {
        return null;
    }
}
