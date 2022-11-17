<?php

namespace App\Actions;

use App\Enums\InteractionActions;
use App\Events\InteractionUpdate;
use App\Services\VoteService;
use App\Traits\MemberHelperTrait;
use App\Traits\RegisterHelperTrait;

class VoteAction implements ActionInterface
{
    use MemberHelperTrait, RegisterHelperTrait;

    private VoteService $service;

    public function __construct()
    {
        $this->service = new VoteService();
    }

    public function canInteract(InteractionActions $action, string $userId, string $targetId = ''): bool
    {
        return $this->alive($targetId, $this->getGameId($targetId));
    }

    public function call(string $targetId, InteractionActions $action): mixed
    {
        return $this->service->vote($targetId, $this->getGameId($targetId));
    }

    private function getGameId(string $userId): string
    {
        return $this->getCurrentUserGameActivity($userId);
    }

    public function updateClients(InteractionActions $action, string $userId): void
    {
        broadcast(new InteractionUpdate([
            'type' => $action->value,
            'votedPlayers' => $this->service->getVotes($this->getGameId($userId)),
        ]));
    }
}
