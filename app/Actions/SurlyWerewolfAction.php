<?php

namespace App\Actions;

use App\Enums\InteractionAction;
use App\Enums\Role;
use App\Events\InteractionUpdate;
use App\Facades\Redis;
use App\Traits\MemberHelperTrait;

class SurlyWerewolfAction implements ActionInterface
{
    use MemberHelperTrait;

    public function __construct(
        private readonly string $gameId
    ) {
    }

    public function isSingleUse(): bool
    {
        return true;
    }

    public function canInteract(InteractionAction $action, string $userId, string $targetId = ''): bool
    {
        $actionCondition = match ($action) {
            InteractionAction::Bite => $this->alive($targetId, $this->gameId),
            default => true,
        };

        return $this->getRoleByUserId($userId, $this->gameId) === Role::SurlyWerewolf && $actionCondition;
    }

    public function call(string $targetId, InteractionAction $action, string $emitterId): null
    {
        switch ($action) {
            case InteractionAction::SurlySkip:
                break;
            case InteractionAction::Bite:
                $this->bite($targetId, $this->gameId);
                break;
        }

        return null;
    }

    public function bite(string $targetId, string $gameId): void
    {
        if ($this->isUsed(InteractionAction::Bite, $gameId)) {
            return;
        }

        broadcast(new InteractionUpdate([
            'gameId' => $gameId,
            'type' => InteractionAction::Bite->value,
        ], true, [$targetId]));

        $game = Redis::get("game:$gameId");
        $state = Redis::get("game:$gameId:state");

        $game['bitten'] = [
            'target' => $targetId,
            'round' => $state['round'],
        ];

        Redis::set("game:$gameId", $game);

        $usedActions = Redis::get("game:$gameId:interactions:usedActions") ?? [];
        $usedActions[] = InteractionAction::Bite->value;

        Redis::set("game:$gameId:interactions:usedActions", $usedActions);
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

    private function isUsed(InteractionAction $action, string $gameId): bool
    {
        $usedActions = Redis::get("game:$gameId:interactions:usedActions") ?? [];

        return in_array($action->value, $usedActions, true);
    }
}
