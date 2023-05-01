<?php

namespace App\Actions;

use App\Enums\InteractionAction;
use App\Enums\Role;
use App\Enums\State;
use App\Facades\Redis;
use App\Traits\MemberHelperTrait;

class WitchAction implements ActionInterface
{
    use MemberHelperTrait;

    public function __construct(
        private readonly string $gameId
    ) {
    }

    public function canInteract(InteractionAction $action, string $userId, string $targetId = ''): bool
    {
        $actionCondition = match ($action) {
            InteractionAction::KillPotion => $this->alive($targetId, $this->gameId),
            default => true,
        };

        $role = $this->getRole($userId);

        return $role === Role::Witch && $actionCondition;
    }

    public function call(string $targetId, InteractionAction $action, string $emitterId): null
    {
        switch ($action) {
            case InteractionAction::WitchSkip:
                break;
            case InteractionAction::KillPotion:
                $this->killPotion($targetId);
                $this->setUsed($action, $this->gameId);
                break;
            case InteractionAction::RevivePotion:
                $this->revivePotion($targetId);
                $this->setUsed($action, $this->gameId);
                break;
        }

        return null;
    }

    private function setUsed(InteractionAction $action, string $gameId): void
    {
        Redis::update("game:$gameId:interactions:usedActions", function (array &$usedActions) use ($action) {
            $usedActions[] = $action->value;
        });
    }

    private function killPotion(string $targetId): void
    {
        if ($this->isUsed(InteractionAction::KillPotion, $this->gameId)) {
            return;
        }

        $this->kill($targetId, $this->gameId, State::Witch->stringify());
    }

    private function revivePotion(string $targetId): void
    {
        $game = Redis::get("game:$this->gameId");

        if ($this->isUsed(InteractionAction::RevivePotion, $this->gameId)) {
            return;
        }

        if (!array_key_exists('dead_users', $game) && in_array($targetId, $game['dead_users'], true)) {
            return;
        }

        $deaths = Redis::get("game:$this->gameId:deaths") ?? [];

        $index = array_search($targetId, $game['dead_users'], true);
        array_splice($game['dead_users'], (int) $index, 1);
        $deaths = array_filter($deaths, fn ($death) => $death['user'] !== $targetId);

        Redis::set("game:$this->gameId", $game);
        Redis::set("game:$this->gameId:deaths", $deaths);
    }

    private function getRole(string $userId): Role
    {
        return $this->getRoleByUserId($userId, $this->gameId);
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
