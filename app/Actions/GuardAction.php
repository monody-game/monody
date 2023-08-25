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
        $usedActions = Redis::get("game:$this->gameId:interactions:usedActions");

        if (in_array(InteractionAction::Guard->value, $usedActions, true)) {
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

        Redis::update("game:$this->gameId:interactions:usedActions", function (array &$usedActions) {
            $usedActions[] = InteractionAction::Guard->value;
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

        Redis::update("game:$this->gameId:interactions:usedActions", function (array $usedActions) {
            return array_filter($usedActions, fn ($action) => $action !== InteractionAction::Guard->value);
        });

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
