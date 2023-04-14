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

        $actionCondition = match ($action) {
            InteractionAction::Bite => $this->alive($targetId, $gameId),
            default => true,
        };

        return $this->getRoleByUserId($userId, $gameId) === Role::SurlyWerewolf && $actionCondition;
    }

    public function call(string $targetId, InteractionAction $action, string $emitterId): null
    {
        switch ($action) {
            case InteractionAction::SurlySkip:
                break;
            case InteractionAction::Bite:
                $this->bite($targetId, $this->getGameId($targetId));
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
			'round' => $state['round']
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

    private function getGameId(string $userId): string
    {
        return $this->getCurrentUserGameActivity($userId);
    }

    private function isUsed(InteractionAction $action, string $gameId): bool
    {
        $usedActions = Redis::get("game:$gameId:interactions:usedActions") ?? [];

        return in_array($action->value, $usedActions, true);
    }
}
