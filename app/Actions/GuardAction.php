<?php

namespace App\Actions;

use App\Enums\InteractionAction;
use App\Enums\Role;
use App\Facades\Redis;
use App\Traits\MemberHelperTrait;

class GuardAction implements ActionInterface
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
        $game = Redis::get("game:$this->gameId");

        if (array_key_exists('guarded', $game) && $game['guarded'] === $targetId) {
            return false;
        }

        return $this->getRoleByUserId($userId, $this->gameId) === Role::Guard &&
            $this->alive($targetId, $this->gameId);
    }

    /**
     * {@inheritDoc}
     */
    public function call(string $targetId, InteractionAction $action, string $emitterId): string
    {
        $game = Redis::get("game:$this->gameId");

        $game['guarded'] = $targetId;

        Redis::set("game:$this->gameId", $game);

        return $targetId;
    }

    /**
     * {@inheritDoc}
     */
    public function updateClients(string $emitterId): void
    {
        // TODO: Implement updateClients() method.
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
        // TODO: Implement close() method.
    }

    /**
     * {@inheritDoc}
     */
    public function status(string $gameId): null
    {
        return null;
    }
}
