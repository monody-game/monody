<?php

namespace App\Actions;

use App\Enums\GameStates;
use App\Enums\InteractionActions;
use App\Enums\Roles;
use App\Events\GameKill;
use App\Facades\Redis;
use App\Traits\MemberHelperTrait;
use App\Traits\RegisterHelperTrait;

class WitchAction implements ActionInterface
{
    use MemberHelperTrait, RegisterHelperTrait;

    public function canInteract(InteractionActions $action, string $userId, string $targetId = ''): bool
    {
        $gameId = $this->getCurrentUserGameActivity($userId) ?? '';

        switch ($action) {
            case InteractionActions::KillPotion:
                if ($this->alive($targetId, $gameId)) {
                    $actionCondition = true;
                    break;
                }
                $actionCondition = false;
                break;
            case InteractionActions::RevivePotion:
                if ($this->alive($targetId, $gameId)) {
                    $actionCondition = false;
                    break;
                }
                $actionCondition = true;
                break;
            default:
                $actionCondition = true;
        }

        $role = $this->getRole($userId);

        return $role !== false && $role === Roles::Witch && $actionCondition;
    }

    public function call(string $targetId, InteractionActions $action): mixed
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
        $gameId = $this->getCurrentUserGameActivity($targetId) ?? '';
        $success = $this->kill($targetId, $gameId);

        if (!$success) {
            return;
        }

        GameKill::broadcast([
            'killedUser' => $targetId,
            'gameId' => $gameId,
            'context' => GameStates::Witch->stringify(),
        ]);
    }

    private function revivePotion(string $targetId): void
    {
        $gameId = $this->getCurrentUserGameActivity($targetId) ?? '';
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
        return $this->getRoleByUserId($userId, $this->getCurrentUserGameActivity($userId) ?? '');
    }
}
