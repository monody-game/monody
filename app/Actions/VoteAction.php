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

    public function updateClients(string $userId): void
    {
        $gameId = $this->getGameId($userId);
        broadcast(new InteractionUpdate([
            'gameId' => $gameId,
            'type' => InteractionActions::Vote->value,
            'votedPlayers' => $this->service->getVotes($gameId),
        ]));
    }

    public function close(string $gameId): void
    {
        $this->service->afterVote($gameId);
    }

    public function isSingleUse(): bool
    {
        return false;
    }
}
