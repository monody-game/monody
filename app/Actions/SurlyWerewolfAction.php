<?php

namespace App\Actions;

use App\Enums\InteractionAction;
use App\Enums\Role;
use App\Events\InteractionUpdate;
use App\Facades\Redis;
use App\Traits\MemberHelperTrait;
use App\Traits\RegisterHelperTrait;

class SurlyWerewolfAction implements ActionInterface
{
    use RegisterHelperTrait, MemberHelperTrait;

    public function isSingleUse(): bool
    {
        return true;
    }

    public function canInteract(InteractionAction $action, string $userId, string $targetId = ''): bool
    {
        $gameId = $this->getGameId($userId);

        return $this->getUserIdByRole(Role::SurlyWerewolf, $gameId)[0] === $userId && $this->alive($targetId, $gameId);
    }

    public function call(string $targetId, InteractionAction $action, string $emitterId): mixed
    {
        $gameId = $this->getGameId($targetId);

        broadcast(new InteractionUpdate([
            'gameId' => $gameId,
            'type' => InteractionAction::Bite->value,
        ], true, [$targetId]));

        $usedActions = Redis::get("game:$gameId:interactions:usedActions") ?? [];
        $usedActions[] = $action->value;

        Redis::set("game:$gameId:interactions:usedActions", $usedActions);

        return $targetId;
    }

    public function updateClients(string $userId): void
    {
    }

    public function additionnalData(string $gameId): null
    {
        return null;
    }

    public function close(string $gameId): void
    {
    }

    public function status(string $gameId): null
    {
        return null;
    }

    private function getGameId(string $userId): string
    {
        return $this->getCurrentUserGameActivity($userId);
    }
}
