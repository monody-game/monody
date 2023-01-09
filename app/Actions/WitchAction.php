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
        $gameId = $this->getCurrentUserGameActivity($userId);

        $actionCondition = match ($action) {
            InteractionActions::KillPotion => $this->alive($targetId, $gameId),
            InteractionActions::RevivePotion => !$this->alive($targetId, $gameId),
            default => true,
        };

        $role = $this->getRole($userId);

        return $role !== false && $role === Roles::Witch && $actionCondition;
    }

    public function call(string $targetId, InteractionActions $action, ?string $emitterId = null): null
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

        return null;
    }

    private function killPotion(string $targetId): void
    {
        $gameId = $this->getCurrentUserGameActivity($targetId);
        $this->kill($targetId, $gameId, States::Witch->stringify());
    }

    private function revivePotion(string $targetId): void
    {
        $gameId = $this->getCurrentUserGameActivity($targetId);
        $member = $this->getMember($targetId, $gameId);
        $members = $this->getMembers($gameId);
        $index = array_search($member, $members, true);

        if (!$member || false === $index) {
            return;
        }

        $member = array_splice($members, (int) $index, 1)[0];

        $member['user_info']['is_dead'] = false;
        $members = [...$members, $member];

        Redis::set("game:$gameId:members", $members);
    }

    private function getRole(string $userId): Roles|false
    {
        return $this->getRoleByUserId($userId, $this->getCurrentUserGameActivity($userId));
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
}
