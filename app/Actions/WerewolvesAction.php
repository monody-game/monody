<?php

namespace App\Actions;

use App\Enums\InteractionActions;
use App\Enums\States;
use App\Events\GameKill;
use App\Traits\MemberHelperTrait;
use App\Traits\RegisterHelperTrait;

class WerewolvesAction implements ActionInterface
{
    use MemberHelperTrait, RegisterHelperTrait;

    public function canInteract(InteractionActions $action, string $userId, string $targetId = ''): bool
    {
        $gameId = $this->getGameId($userId);

        return in_array($userId, $this->getWerewolves($gameId)[0], true) && $this->alive($targetId, $gameId);
    }

    public function call(string $targetId, InteractionActions $action): mixed
    {
        $gameId = $this->getGameId($targetId);
        $success = $this->kill($targetId, $gameId);

        if (!$success) {
            return null;
        }

        GameKill::broadcast([
            'killedUser' => $targetId,
            'gameId' => $gameId,
            'context' => States::Werewolf->stringify(),
        ]);

        return null;
    }

    private function getGameId(string $userId): string
    {
        return $this->getCurrentUserGameActivity($userId);
    }
}
