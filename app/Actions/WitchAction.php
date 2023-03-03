<?php

namespace App\Actions;

use App\Enums\InteractionActions;
use App\Enums\Roles;
use App\Enums\States;
use App\Traits\InteractsWithRedis;
use App\Traits\MemberHelperTrait;
use App\Traits\RegisterHelperTrait;

class WitchAction implements ActionInterface
{
    use MemberHelperTrait, RegisterHelperTrait, InteractsWithRedis;

    public function canInteract(InteractionActions $action, string $userId, string $targetId = ''): bool
    {
        $gameId = $this->getGameId($userId);

        $actionCondition = match ($action) {
            InteractionActions::KillPotion => $this->alive($targetId, $gameId),
            default => true,
        };

        $role = $this->getRole($userId);

        return $role === Roles::Witch && $actionCondition;
    }

    public function call(string $targetId, InteractionActions $action, string $emitterId): null
    {
        $gameId = $this->getGameId($targetId);
        switch ($action) {
            case InteractionActions::WitchSkip:
                break;
            case InteractionActions::KillPotion:
                $this->killPotion($targetId);
                break;
            case InteractionActions::RevivePotion:
                $this->revivePotion($targetId);
                break;
        }

        $this->setUsed($action, $gameId);

        return null;
    }

    private function setUsed(InteractionActions $action, string $gameId): void
    {
        $usedActions = $this->redis()->get("game:$gameId:interactions:usedActions") ?? [];
        $usedActions[] = $action->value;

        $this->redis()->set("game:$gameId:interactions:usedActions", $usedActions);
    }

    private function killPotion(string $targetId): void
    {
        $gameId = $this->getGameId($targetId);

        if ($this->isUsed(InteractionActions::KillPotion, $gameId)) {
            return;
        }

        $this->kill($targetId, $gameId, States::Witch->stringify());
    }

    private function revivePotion(string $targetId): void
    {
        $gameId = $this->getGameId($targetId);
        $game = $this->redis()->get("game:$gameId");

        if ($this->isUsed(InteractionActions::RevivePotion, $gameId)) {
            return;
        }

        if (!array_key_exists('dead_users', $game) && in_array($targetId, $game['dead_users'], true)) {
            return;
        }

        $deaths = $this->redis()->get("game:$gameId:deaths") ?? [];

        $index = array_search($targetId, $game['dead_users'], true);
        array_splice($game['dead_users'], (int) $index, 1);
        $deaths = array_filter($deaths, fn ($death) => $death['user'] !== $targetId);

        $this->redis()->set("game:$gameId", $game);
        $this->redis()->set("game:$gameId:deaths", $deaths);
    }

    private function getRole(string $userId): Roles
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
        $deaths = $this->redis()->get("game:$gameId:deaths") ?? [];

        return array_map(fn ($death) => $death['user'], $deaths);
    }

    private function isUsed(InteractionActions $action, string $gameId): bool
    {
        $usedActions = $this->redis()->get("game:$gameId:interactions:usedActions") ?? [];

        return in_array($action->value, $usedActions, true);
    }

    public function status(string $gameId): null
    {
        return null;
    }
}
