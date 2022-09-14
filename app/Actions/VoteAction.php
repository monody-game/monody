<?php

namespace App\Actions;

use App\Enums\InteractionActions;
use App\Services\VoteService;
use App\Traits\MemberHelperTrait;
use App\Traits\RegisterHelperTrait;

class VoteAction implements ActionInterface
{
    use MemberHelperTrait, RegisterHelperTrait;

    public function canInteract(InteractionActions $action, string $userId, string $targetId = ''): bool
    {
        return $this->alive($targetId, $this->getGameId($targetId));
    }

    public function call(string $targetId, InteractionActions $action): mixed
    {
        $service = new VoteService;

        return $service->vote($targetId, $this->getGameId($targetId));
    }

    private function getGameId(string $userId): string
    {
        return $this->getCurrentUserGameActivity($userId);
    }
}
