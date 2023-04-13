<?php

namespace App\Actions;

use App\Enums\InteractionAction;
use App\Enums\Role;
use App\Enums\State;
use App\Facades\Redis;
use App\Traits\MemberHelperTrait;
use App\Traits\RegisterHelperTrait;

class WitchAction implements ActionInterface
{
    use MemberHelperTrait, RegisterHelperTrait;

    public function canInteract(InteractionAction $action, string $userId, string $targetId = ''): bool
    {
        $gameId = $this->getGameId($userId);

        $actionCondition = match ($action) {
            InteractionAction::KillPotion => $this->alive($targetId, $gameId),
            default => true,
        };

        $role = $this->getRole($userId);

        return $role === Role::Witch && $actionCondition;
    }

    public function call(string $targetId, InteractionAction $action, string $emitterId): null
    {
        $gameId = $this->getGameId($targetId);
        $this->setUsed($action, $gameId);

        switch ($action) {
            case InteractionAction::WitchSkip:
                break;
            case InteractionAction::KillPotion:
                $this->killPotion($targetId);
                break;
            case InteractionAction::RevivePotion:
                $this->revivePotion($targetId);
                break;
        }

        return null;
    }

    private function setUsed(InteractionAction $action, string $gameId): void
    {
        $usedActions = Redis::get("game:$gameId:interactions:usedActions") ?? [];
        $usedActions[] = $action->value;

        Redis::set("game:$gameId:interactions:usedActions", $usedActions);
    }

    private function killPotion(string $targetId): void
    {
        $gameId = $this->getGameId($targetId);

        if ($this->isUsed(InteractionAction::KillPotion, $gameId)) {
            return;
        }

        $this->kill($targetId, $gameId, State::Witch->stringify());
    }

    private function revivePotion(string $targetId): void
    {
        $gameId = $this->getGameId($targetId);
        $game = Redis::get("game:$gameId");

        if ($this->isUsed(InteractionAction::RevivePotion, $gameId)) {
            return;
        }

        if (!array_key_exists('dead_users', $game) && in_array($targetId, $game['dead_users'], true)) {
            return;
        }

        $deaths = Redis::get("game:$gameId:deaths") ?? [];

        $index = array_search($targetId, $game['dead_users'], true);
        array_splice($game['dead_users'], (int) $index, 1);
        $deaths = array_filter($deaths, fn ($death) => $death['user'] !== $targetId);

        Redis::set("game:$gameId", $game);
        Redis::set("game:$gameId:deaths", $deaths);
    }

    private function getRole(string $userId): Role
    {
        return $this->getRoleByUserId($userId, $this->getGameId($userId));
    }

    private function getGameId(string $userId): string
    {
        return $this->getCurrentUserGameActivity($userId);
    }

    public function updateClients(string $userId): void
    {
    }

    public function close(string $gameId): void
    {
    }

    public function isSingleUse(): bool
    {
        return true;
    }

    public function additionnalData(string $gameId): array
    {
        $deaths = Redis::get("game:$gameId:deaths") ?? [];

        return array_map(fn ($death) => $death['user'], $deaths);
    }

    private function isUsed(InteractionAction $action, string $gameId): bool
    {
        $usedActions = Redis::get("game:$gameId:interactions:usedActions") ?? [];

        return in_array($action->value, $usedActions, true);
    }

    public function status(string $gameId): null
    {
        return null;
    }
}
