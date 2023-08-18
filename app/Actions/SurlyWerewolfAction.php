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
        if ($this->isUsed(InteractionAction::Bite)) {
            return;
        }

        broadcast(new InteractionUpdate([
            'gameId' => $gameId,
            'type' => InteractionAction::Bite->value,
        ], true, [$targetId]));

        Redis::update("game:$gameId", function (array &$game) use ($gameId, $targetId) {
            $state = Redis::get("game:$gameId:state");

            $game['bitten'] = [
                'target' => $targetId,
                'round' => $state['round'],
            ];
        });

        Redis::update("game:$gameId:interactions:usedActions", function (array &$usedActions) {
            $usedActions[] = InteractionAction::Bite->value;
        });
    }

    public function updateClients(string $userId): void
    {
    }

    public function additionnalData(): null
    {
        return null;
    }

    public function close(): void
    {
    }

    public function status(): null
    {
        return null;
    }

    private function isUsed(InteractionAction $action): bool
    {
        $usedActions = Redis::get("game:$this->gameId:interactions:usedActions") ?? [];

        return in_array($action->value, $usedActions, true);
    }
}
