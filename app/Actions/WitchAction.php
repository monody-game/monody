<?php

namespace App\Actions;

use App\Enums\InteractionActions;
use App\Enums\Roles;
use App\Enums\States;
use App\Facades\Redis;
use App\Traits\MemberHelperTrait;
use App\Traits\RegisterHelperTrait;

class WitchAction implements ActionInterface
{
    use MemberHelperTrait, RegisterHelperTrait;

    public function canInteract(InteractionActions $action, string $userId, string $targetId = ''): bool
    {
        $gameId = $this->getGameId($userId);
        $deaths = Redis::get("game:$gameId:deaths") ?? [];

        $actionCondition = match ($action) {
            InteractionActions::KillPotion => $this->alive($targetId, $gameId),
            InteractionActions::RevivePotion => !$this->alive($targetId, $gameId) && array_filter($deaths, fn ($death) => $death['user'] === $userId) !== [],
            default => true,
        };

        $role = $this->getRole($userId);

        return $role !== false && $role === Roles::Witch && $actionCondition;
    }

    public function call(string $targetId, InteractionActions $action, string $emitterId): null
    {
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

        $this->setUsed($action, $this->getGameId($targetId));

        return null;
    }

    private function setUsed(InteractionActions $action, string $gameId): void
    {
        $usedActions = Redis::get("game:$gameId:interactions:usedActions") ?? [];
        $usedActions[] = $action->value;

        Redis::set("game:$gameId:interactions:usedActions", $usedActions);
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

        if ($this->isUsed(InteractionActions::RevivePotion, $gameId)) {
            return;
        }

        $member = $this->getMember($targetId, $gameId);
        $members = $this->getMembers($gameId);
        $index = array_search($member, $members, true);
        $deaths = Redis::get("game:$gameId:deaths") ?? [];

        if (!$member || false === $index) {
            return;
        }

        $member = array_splice($members, (int) $index, 1)[0];
        $deaths = array_filter($deaths, fn ($death) => $death['user'] !== $targetId);

        $member['user_info']['is_dead'] = false;
        $members = [...$members, $member];

        Redis::set("game:$gameId:members", $members);
        Redis::set("game:$gameId:deaths", $deaths);
    }

    private function getRole(string $userId): Roles|false
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

    private function isUsed(InteractionActions $action, string $gameId): bool
    {
        $usedActions = Redis::get("game:$gameId:interactions:usedActions") ?? [];

        return in_array($action->value, $usedActions, true);
    }
}
