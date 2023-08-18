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
        Redis::update("game:$this->gameId", function (array &$game) use ($targetId) {
            $game['guarded'] = $targetId;
        });

        return $targetId;
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
    public function additionnalData(): ?string
    {
        $game = Redis::get("game:$this->gameId");

        if (array_key_exists('guarded', $game)) {
            return $game['guarded'];
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function close(): void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function status(): null
    {
        return null;
    }
}
